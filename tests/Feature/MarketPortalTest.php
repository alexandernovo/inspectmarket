<?php

use App\Models\Announcement;
use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\LivestockInspection;
use App\Models\MarketMessage;
use App\Models\MarketNotification;
use App\Models\Payment;
use App\Models\StallApplication;
use App\Models\StallApplicationDocument;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationCode;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('shows the wireframe homepage and opens the five-role login page', function () {
    SystemSetting::updateOrCreate(
        ['key' => 'market_tagline'],
        ['value' => 'Backend managed market service information', 'group' => 'GENERAL']
    );

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('PANDAN PUBLIC MARKET')
        ->assertSee('Backend managed market service information')
        ->assertSee('Sign in')
        ->assertSee('Log in')
        ->assertDontSee('Explore services')
        ->assertDontSee('Administrator');

    $this->get(route('public.roles', 'login'))
        ->assertOk()
        ->assertSee('User-Login')
        ->assertSee('Administrator')
        ->assertSee('Treasurer')
        ->assertSee('Inspector')
        ->assertSee('Clerk')
        ->assertSee('Tenant');

    $this->get(route('public.roles', 'register'))
        ->assertOk()
        ->assertSee('Create an Account')
        ->assertSee('#register-tenant', false)
        ->assertSee('Register your phone number');
});

it('lets every seeded role open only its own dashboard', function (string $role) {
    $user = User::where('usertype', strtoupper($role))->firstOrFail();

    $this->actingAs($user)
        ->get(route("{$role}.dashboard"))
        ->assertOk()
        ->assertSee(ucfirst($role).' Dashboard');
})->with([
    'administrator',
    'treasurer',
    'inspector',
    'clerk',
    'tenant',
]);

it('blocks users from another role portal', function () {
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();

    $this->actingAs($tenant)
        ->get(route('administrator.dashboard'))
        ->assertForbidden();
});

it('authenticates a seeded user through the matching role portal', function () {
    $this->post(route('portal.login.store', ['role' => 'tenant']), [
        'username' => 'tenant',
        'password' => 'password',
    ])->assertRedirect(route('tenant.dashboard'));

    $this->assertAuthenticated();
});

it('allows a tenant to submit a stall application', function () {
    Storage::fake('public');
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();

    $this->actingAs($tenant)
        ->withHeader('Accept', 'application/json')
        ->post(route('tenant.applications.store'), [
            'business_name' => 'Test Market Store',
            'business_category' => 'Dry goods',
            'business_address' => 'Pandan, Antique',
            'preferred_section' => 'MIXED',
            'preferred_stall_number' => 12,
            'business_owner' => $tenant->full_name,
            'tin_number' => '123-456-789-000',
            'contact_number' => $tenant->phone_num,
            'documents' => [
                UploadedFile::fake()->create('tenant-permit.pdf', 100, 'application/pdf'),
            ],
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $application = StallApplication::where('business_name', 'Test Market Store')->firstOrFail();
    $document = StallApplicationDocument::where('stall_application_id', $application->id)->firstOrFail();

    expect($application->tenant_id)->toBe($tenant->id)
        ->and($application->tin_number)->toBe('123-456-789-000');
    Storage::disk('public')->assertExists($document->path);

    $this->actingAs($tenant)
        ->get(route('stall-applications.documents.preview', $document))
        ->assertOk()
        ->assertHeader('content-disposition', 'inline; filename="tenant-permit.pdf"');
});

it('allows a tenant to delete only their pending stall application', function () {
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();
    $application = $tenant->stallApplications()->create([
        'application_number' => 'APP-DELETE-TEST',
        'business_name' => 'Pending Store',
        'business_category' => 'Dry goods',
        'business_address' => 'Pandan, Antique',
        'preferred_section' => 'MIXED',
        'business_owner' => $tenant->full_name,
        'contact_number' => $tenant->phone_num,
        'status' => 'PENDING',
    ]);

    $this->actingAs($tenant)
        ->delete(route('tenant.applications.destroy', $application))
        ->assertRedirect();

    $this->assertDatabaseMissing('stall_applications', ['id' => $application->id]);
});

it('lets tenants manage pending inspection requests from backend table actions', function () {
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();
    $inspection = $tenant->inspectionRequests()->create([
        'request_number' => 'INSP-TENANT-ACTIONS',
        'livestock_type' => 'POULTRY',
        'owner_name' => $tenant->full_name,
        'address' => 'Pandan, Antique',
        'contact_number' => $tenant->phone_num,
        'scheduled_at' => now()->addDays(4),
        'animal_count' => 2,
        'status' => 'PENDING',
    ]);

    $this->actingAs($tenant)
        ->getJson(route('tenant.datatable.inspections', [
            'draw' => 1,
            'start' => 0,
            'length' => 20,
            'search' => ['value' => 'INSP-TENANT-ACTIONS', 'regex' => false],
        ]))
        ->assertOk()
        ->assertJsonFragment(['reference' => 'INSP-TENANT-ACTIONS'])
        ->assertJsonPath('data.0.action', fn (string $actions) => str_contains($actions, 'js-view-inspection')
            && str_contains($actions, 'js-edit-inspection')
            && str_contains($actions, 'js-delete-inspection'));

    $this->actingAs($tenant)
        ->withHeader('Accept', 'application/json')
        ->put(route('tenant.inspections.update', $inspection), [
            'livestock_type' => 'BEEF',
            'owner_name' => $tenant->full_name,
            'address' => 'Centro, Pandan, Antique',
            'contact_number' => $tenant->phone_num,
            'scheduled_at' => now()->addDays(6)->format('Y-m-d H:i:s'),
            'animal_count' => 3,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseHas('livestock_inspections', [
        'id' => $inspection->id,
        'livestock_type' => 'BEEF',
        'animal_count' => 3,
    ]);

    $this->actingAs($tenant)
        ->withHeader('Accept', 'application/json')
        ->delete(route('tenant.inspections.destroy', $inspection))
        ->assertOk();

    $this->assertDatabaseMissing('livestock_inspections', ['id' => $inspection->id]);
});

it('runs the inspector wireframe workflow against shared tenant records', function () {
    $inspector = User::where('usertype', User::ROLE_INSPECTOR)->firstOrFail();
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();
    $scheduledAt = now()->addDays(8)->setTime(14, 30);

    $this->actingAs($inspector)
        ->withHeader('Accept', 'application/json')
        ->post(route('inspector.inspections.store'), [
            'livestock_type' => 'POULTRY',
            'owner_name' => 'Inspector Added Owner',
            'address' => 'Pandan, Antique',
            'contact_number' => '09170001111',
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            'animal_count' => 6,
            'breed' => 'Chicken (Native)',
            'source_location' => 'Pandan Farm',
            'purpose' => 'Human Consumption',
            'ante_mortem_findings' => ['general_condition' => 'HEALTHY'],
            'post_mortem_findings' => ['general_condition' => 'PASSED', 'organs_examination' => 'PASSED'],
            'inspection_result' => 'PASSED',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $created = LivestockInspection::where('owner_name', 'Inspector Added Owner')->firstOrFail();
    expect($created->inspector_id)->toBe($inspector->id)
        ->and($created->request_source)->toBe('INSPECTOR')
        ->and($created->status)->toBe('COMPLETED')
        ->and($created->certificate_number)->not->toBeNull();

    $tenantRequest = $tenant->inspectionRequests()->create([
        'request_number' => 'INSP-INSPECTOR-DECISION',
        'livestock_type' => 'BEEF',
        'owner_name' => $tenant->full_name,
        'address' => $tenant->address,
        'contact_number' => $tenant->phone_num,
        'email' => $tenant->email,
        'request_source' => 'TENANT',
        'scheduled_at' => now()->addDays(10),
        'animal_count' => 2,
        'status' => 'PENDING',
    ]);

    $this->actingAs($inspector)
        ->withHeader('Accept', 'application/json')
        ->put(route('inspector.inspections.update', $tenantRequest), [
            'status' => 'APPROVED',
            'scheduled_at' => now()->addDays(11)->format('Y-m-d H:i:s'),
            'remarks' => 'Approved inspection schedule.',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    expect($tenantRequest->fresh()->status)->toBe('APPROVED')
        ->and($tenantRequest->fresh()->inspector_id)->toBe($inspector->id)
        ->and(MarketNotification::where('user_id', $tenant->id)->where('message', 'like', '%INSP-INSPECTOR-DECISION%')->exists())->toBeTrue();

    $this->actingAs($inspector)
        ->getJson(route('inspector.datatable.inspections', [
            'draw' => 1,
            'start' => 0,
            'length' => 20,
            'mode' => 'requests',
            'status' => 'APPROVED',
            'search' => ['value' => 'INSP-INSPECTOR-DECISION', 'regex' => false],
        ]))
        ->assertOk()
        ->assertJsonPath('data.0.reference', 'INSP-INSPECTOR-DECISION')
        ->assertJsonPath('data.0.action', fn (string $action) => str_contains($action, 'js-request-view'));

    $this->actingAs($inspector)
        ->get(route('inspector.reports', [
            'livestock' => 'POULTRY',
            'month' => $scheduledAt->format('Y-m'),
        ]))
        ->assertOk()
        ->assertSee('Inspector Added Owner');

    foreach (['doc', 'xls'] as $format) {
        $this->actingAs($inspector)
            ->get(route('inspector.reports.export', [
                'format' => $format,
                'livestock' => 'POULTRY',
                'month' => $scheduledAt->format('Y-m'),
            ]))
            ->assertOk()
            ->assertDownload();
    }

    $this->actingAs($inspector)
        ->withHeader('Accept', 'application/json')
        ->delete(route('inspector.inspections.destroy', $created))
        ->assertOk();

    $this->assertDatabaseMissing('livestock_inspections', ['id' => $created->id]);
});

it('supports the clerk inspector and treasurer write workflows', function () {
    $clerk = User::where('usertype', User::ROLE_CLERK)->firstOrFail();
    $inspector = User::where('usertype', User::ROLE_INSPECTOR)->firstOrFail();
    $treasurer = User::where('usertype', User::ROLE_TREASURER)->firstOrFail();
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();
    $inspection = LivestockInspection::where('status', 'PENDING')->firstOrFail();

    $this->actingAs($clerk)->post(route('clerk.collections.store'), [
        'collector_id' => $clerk->id,
        'stall_section' => 'FISH',
        'ticket_quantity' => 10,
        'amount' => 1000,
        'collection_date' => now()->toDateString(),
    ])->assertRedirect();

    $this->actingAs($inspector)->put(route('inspector.inspections.update', $inspection), [
        'status' => 'COMPLETED',
        'inspection_result' => 'PASSED',
        'findings' => 'Passed the sanitary inspection.',
    ])->assertRedirect();

    $this->actingAs($treasurer)->post(route('treasurer.announcements.store'), [
        'category' => 'OTHERS',
        'title' => 'Testing announcement',
        'content' => 'This announcement verifies the publication workflow.',
    ])->assertRedirect();

    $this->actingAs($treasurer)->post(route('treasurer.payments.store'), [
        'tenant_id' => $tenant->id,
        'amount' => 750,
        'period_month' => now()->format('Y-m'),
        'due_date' => now()->addDays(10)->toDateString(),
        'payment_method' => 'CASH',
        'status' => 'PAID',
    ])->assertRedirect();

    expect(CashTicketCollection::where('ticket_quantity', 10)->exists())->toBeTrue()
        ->and($inspection->fresh()->status)->toBe('COMPLETED')
        ->and(Announcement::where('title', 'Testing announcement')->exists())->toBeTrue()
        ->and(Payment::where('tenant_id', $tenant->id)->where('status', 'PAID')->count())->toBeGreaterThan(0);
});

it('shows the same clerk step one cash ticket remarks in the treasurer view', function () {
    $clerk = User::where('usertype', User::ROLE_CLERK)->firstOrFail();
    $treasurer = User::where('usertype', User::ROLE_TREASURER)->firstOrFail();
    $note = 'Treasurer should see this exact step one note.';

    $this->actingAs($clerk)->post(route('clerk.assignments.store'), [
        'workflow_status' => 'REQUESTED',
        'assignments' => [
            [
                'collector_id' => $clerk->id,
                'stall_section' => 'MIXED',
                'ticket_start' => 1,
                'ticket_end' => 20,
                'assigned_date' => now()->toDateString(),
                'remarks' => $note,
            ],
        ],
        'cash_slip' => [
            [
                'unit' => 'Cash Ticket',
                'description' => 'Cash Ticket for Public Market Collection',
                'stub' => 1,
                'pcs' => 20,
            ],
        ],
    ])->assertRedirect();

    $assignment = CashTicketAssignment::where('remarks', 'like', '%'.$note.'%')->firstOrFail();

    expect($assignment->remarks)->toContain('Remarks: '.$note);

    $this->actingAs($treasurer)
        ->get(route('treasurer.assignments'))
        ->assertOk()
        ->assertSee($note)
        ->assertDontSee('RCC II: '.$clerk->full_name);
});

it('lets the clerk delete each cash ticket request step to reset card progress', function () {
    $clerk = User::where('usertype', User::ROLE_CLERK)->firstOrFail();
    $month = now()->startOfMonth();

    $requested = CashTicketAssignment::create([
        'assignment_number' => 'CTA-RESET-REQUESTED',
        'collector_id' => $clerk->id,
        'assigned_by' => $clerk->id,
        'stall_section' => 'FISH',
        'ticket_start' => 1,
        'ticket_end' => 20,
        'ticket_quantity' => 20,
        'assigned_date' => $month,
        'status' => 'REQUESTED',
    ]);
    $assigned = CashTicketAssignment::create([
        'assignment_number' => 'CTA-RESET-ASSIGNED',
        'collector_id' => $clerk->id,
        'assigned_by' => $clerk->id,
        'stall_section' => 'PORK',
        'ticket_start' => 21,
        'ticket_end' => 40,
        'ticket_quantity' => 20,
        'assigned_date' => $month,
        'status' => 'COMPLETED',
    ]);
    $collection = CashTicketCollection::create([
        'collection_number' => 'CT-RESET-WORKFLOW',
        'collector_id' => $clerk->id,
        'recorded_by' => $clerk->id,
        'stall_section' => 'PORK',
        'ticket_quantity' => 20,
        'amount' => 200,
        'collection_date' => $month,
        'status' => 'SUBMITTED',
        'cash_ticket_assignment_id' => $assigned->id,
    ]);

    $reset = fn (int $step) => $this->actingAs($clerk)->delete(route('clerk.cash-ticket.reset'), [
        'month' => $month->toDateString(),
        'step' => $step,
    ])->assertRedirect(route('clerk.collections', [
        'collection_month' => $month->month,
        'collection_year' => $month->year,
    ]));

    $reset(5);
    expect($collection->fresh()->status)->toBe('REPORTED');

    $reset(4);
    expect($collection->fresh()->status)->toBe('RECORDED');

    $reset(3);
    expect($collection->fresh())->toBeNull()
        ->and($assigned->fresh()->status)->toBe('ASSIGNED')
        ->and($requested->fresh())->not->toBeNull();

    $replacementCollection = CashTicketCollection::create([
        'collection_number' => 'CT-RESET-REPLACEMENT',
        'collector_id' => $clerk->id,
        'recorded_by' => $clerk->id,
        'stall_section' => 'PORK',
        'ticket_quantity' => 20,
        'amount' => 200,
        'collection_date' => $month,
        'status' => 'RECORDED',
        'cash_ticket_assignment_id' => $assigned->id,
    ]);
    $assigned->update(['status' => 'COMPLETED']);

    $reset(2);
    expect($replacementCollection->fresh())->toBeNull()
        ->and($assigned->fresh())->toBeNull()
        ->and($requested->fresh())->not->toBeNull();

    $reset(1);
    expect($requested->fresh())->toBeNull();
});

it('prepares inspector add forms with current date and inspection number preview', function () {
    $inspector = User::where('usertype', User::ROLE_INSPECTOR)->firstOrFail();

    $this->actingAs($inspector)
        ->get(route('inspector.inspections', ['type' => 'poultry']))
        ->assertOk()
        ->assertSee('function currentDateTimeLocal', false)
        ->assertSee('function previewInspectionNumber', false)
        ->assertSee('INSP-${date}-${suffix}', false);
});

it('renders the clerk profile with the inspector style profile layout', function () {
    $clerk = User::where('usertype', User::ROLE_CLERK)->firstOrFail();

    $this->actingAs($clerk)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('inspector-profile-layout')
        ->assertSee($clerk->designation)
        ->assertSee('C-Clerk.png');
});

it('completes tenant phone verification and account registration', function () {
    $this->post(route('account.register.code', ['role' => 'tenant']), [
        'phone_num' => '09990001111',
    ])->assertRedirect(route('account.verify.form'));

    $code = VerificationCode::where('phone_num', '09990001111')->latest()->firstOrFail()->code;

    $this->post(route('account.verify'), ['code' => $code])
        ->assertRedirect(route('account.password.form'));

    $this->post(route('account.store'), [
        'firstname' => 'New',
        'lastname' => 'Tenant',
        'username' => 'newtenant',
        'email' => 'newtenant@example.test',
        'address' => 'Pandan, Antique',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('tenant.dashboard'));

    $this->assertAuthenticated();
    expect(User::where('username', 'newtenant')->whereNotNull('phone_verified_at')->exists())->toBeTrue();
});

it('runs homepage signup by ajax while sms is disabled for tests', function () {
    config([
        'services.twilio.enabled' => false,
        'services.twilio.sid' => 'AC_test_sid',
        'services.twilio.auth_token' => 'test-token',
        'services.twilio.from' => '+15550001111',
    ]);

    Http::fake();

    $this->postJson(route('account.register.code', ['role' => 'tenant']), [
        'phone_num' => '09990002222',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Verification code sent.')
        ->assertJsonPath('phone', '09990002222')
        ->assertJsonStructure(['debug_code']);

    Http::assertNothingSent();

    $code = VerificationCode::where('phone_num', '09990002222')
        ->where('purpose', 'REGISTER')
        ->latest()
        ->firstOrFail()
        ->code;

    $this->postJson(route('account.verify'), ['code' => $code])
        ->assertOk()
        ->assertJsonPath('message', 'Phone number verified.')
        ->assertJsonPath('next', 'password');

    $this->postJson(route('account.store'), [
        'firstname' => 'Ajax',
        'lastname' => 'Tenant',
        'username' => 'ajaxtenant',
        'email' => 'ajaxtenant@example.test',
        'address' => 'Pandan, Antique',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Your tenant account is ready.')
        ->assertJsonPath('redirect', route('tenant.dashboard'));

    $this->assertAuthenticated();
    expect(User::where('username', 'ajaxtenant')->whereNotNull('phone_verified_at')->exists())->toBeTrue();
});

it('resets a password with a verified phone code', function () {
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();

    $this->post(route('account.reset.code'), ['phone_num' => $tenant->phone_num])
        ->assertRedirect(route('account.verify.form'));

    $code = VerificationCode::where('phone_num', $tenant->phone_num)->where('purpose', 'RESET')->latest()->firstOrFail()->code;
    $this->post(route('account.verify'), ['code' => $code])
        ->assertRedirect(route('account.reset.form'));

    $this->post(route('account.reset'), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('portal.login', ['role' => 'tenant']));

    expect(Hash::check('new-password', $tenant->fresh()->password))->toBeTrue();
});

it('supports profiles chat uploads notifications and public contact', function () {
    Storage::fake('public');
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();
    $administrator = User::where('usertype', User::ROLE_ADMINISTRATOR)->firstOrFail();

    $this->post(route('contact.store'), [
        'name' => 'Public Visitor',
        'contact_number' => '09992223333',
        'message' => 'I would like to ask about available stalls.',
    ])->assertRedirect();

    $this->actingAs($tenant)->put(route('profile.update'), [
        'firstname' => $tenant->firstname,
        'middlename' => $tenant->middlename,
        'lastname' => $tenant->lastname,
        'username' => $tenant->username,
        'email' => $tenant->email,
        'address' => 'Updated Pandan Address',
        'phone_num' => $tenant->phone_num,
        'profile_image' => UploadedFile::fake()->create('profile.jpg', 10, 'image/jpeg'),
    ])->assertRedirect();

    $this->actingAs($tenant)->post(route('chat.store'), [
        'recipient_id' => $administrator->id,
        'body' => 'Please review my stall application.',
    ])->assertRedirect(route('chat.index', ['user' => $administrator->id]));

    expect($tenant->fresh()->address)->toBe('Updated Pandan Address')
        ->and(MarketMessage::where('sender_id', $tenant->id)->where('recipient_id', $administrator->id)->exists())->toBeTrue();
});

it('supports ticket assignments payment receipt uploads and exports', function () {
    Storage::fake('public');
    $treasurer = User::where('usertype', User::ROLE_TREASURER)->firstOrFail();
    $clerk = User::where('usertype', User::ROLE_CLERK)->firstOrFail();
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();
    $payment = Payment::where('tenant_id', $tenant->id)->where('status', 'PENDING')->firstOrFail();

    $this->actingAs($treasurer)->post(route('treasurer.assignments.store'), [
        'collector_id' => $clerk->id,
        'stall_section' => 'MIXED',
        'ticket_start' => 101,
        'ticket_end' => 120,
        'assigned_date' => now()->toDateString(),
    ])->assertRedirect();

    $this->actingAs($tenant)->post(route('tenant.payments.receipt', $payment), [
        'payment_method' => 'GCASH',
        'receipt' => UploadedFile::fake()->create('receipt.jpg', 10, 'image/jpeg'),
    ])->assertRedirect();

    $this->actingAs($treasurer)->get(route('reports.csv', ['report' => 'cash-ticket']))
        ->assertOk()
        ->assertDownload();

    expect(CashTicketAssignment::where('ticket_start', 101)->where('ticket_end', 120)->exists())->toBeTrue()
        ->and($payment->fresh()->status)->toBe('SUBMITTED');
});

it('renders the complete role screens and server-side tables', function () {
    $administrator = User::where('usertype', User::ROLE_ADMINISTRATOR)->firstOrFail();
    $treasurer = User::where('usertype', User::ROLE_TREASURER)->firstOrFail();
    $clerk = User::where('usertype', User::ROLE_CLERK)->firstOrFail();
    $inspector = User::where('usertype', User::ROLE_INSPECTOR)->firstOrFail();
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();

    foreach ([
        [$tenant, 'tenant.stall-map'],
        [$tenant, 'tenant.applications'],
        [$tenant, 'tenant.payments'],
        [$tenant, 'tenant.inspections'],
        [$inspector, 'inspector.inspections'],
        [$clerk, 'clerk.collections'],
        [$clerk, 'clerk.rentals'],
        [$treasurer, 'treasurer.announcements'],
        [$treasurer, 'treasurer.assignments'],
        [$treasurer, 'treasurer.rentals'],
        [$treasurer, 'treasurer.payments'],
        [$administrator, 'administrator.reports'],
        [$administrator, 'administrator.stall-map'],
        [$administrator, 'administrator.contacts'],
        [$administrator, 'administrator.settings'],
    ] as [$user, $route]) {
        $this->actingAs($user)->get(route($route))->assertOk();
    }

    $this->actingAs($administrator)
        ->getJson(route('administrator.datatable.applications', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]))
        ->assertOk()
        ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
});

it('renders every public wireframe service screen and accepts a public inspection request', function () {
    foreach ([
        'contact',
        'stall-rental',
        'stall-location',
        'stall-application',
        'slaughtered-inspection',
        'inspection-request',
        'announcements',
    ] as $screen) {
        $this->get(route('public.service', $screen))->assertOk();
    }

    $this->get(route('public.service', 'stall-location'))
        ->assertOk()
        ->assertSee('data-rate="750.00"', false)
        ->assertSee('FISH SECTION')
        ->assertDontSee('configured from the backend');

    $this->post(route('public.inspection.store'), [
        'livestock_type' => 'POULTRY',
        'owner_name' => 'Public Requestor',
        'address' => 'Pandan, Antique',
        'contact_number' => '09171234567',
        'email' => 'requestor@example.test',
        'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'animal_count' => 4,
    ])->assertRedirect();

    $this->assertDatabaseHas('livestock_inspections', [
        'owner_name' => 'Public Requestor',
        'request_source' => 'PUBLIC',
        'tenant_id' => null,
    ]);

    Storage::fake('public');
    $this->post(route('public.stall-application.store'), [
        'business_owner' => 'Public Applicant',
        'tin_number' => '987-654-321-000',
        'birth_date' => '1990-01-15',
        'civil_status' => 'SINGLE',
        'sex' => 'MALE',
        'email' => 'applicant@example.test',
        'contact_number' => '09170000001',
        'business_address' => 'Pandan, Antique',
        'business_name' => 'Public Fresh Goods',
        'business_category' => 'Retail',
        'business_nature' => 'Fresh produce retail',
        'trade_name' => 'Fresh Goods',
        'permit_issued_at' => now()->subMonth()->toDateString(),
        'preferred_section' => 'MIXED',
        'preferred_stall_number' => 12,
        'documents' => [
            UploadedFile::fake()->create('barangay-permit.pdf', 300, 'application/pdf'),
            UploadedFile::fake()->create('business-profile.docx', 300, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ],
    ])->assertRedirect();

    $application = StallApplication::where('business_owner', 'Public Applicant')->firstOrFail();
    expect($application->tenant_id)->toBeNull()
        ->and($application->request_source)->toBe('PUBLIC')
        ->and($application->tin_number)->toBe('987-654-321-000')
        ->and($application->documents)->toHaveCount(2);
    $application->documents->each(fn ($document) => Storage::disk('public')->assertExists($document->path));
});

it('connects homepage inspection requests to the logged in tenant', function () {
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();

    $this->actingAs($tenant)
        ->post(route('public.inspection.store'), [
            'livestock_type' => 'BEEF',
            'owner_name' => $tenant->full_name,
            'address' => 'Pandan, Antique',
            'contact_number' => $tenant->phone_num,
            'email' => $tenant->email,
            'scheduled_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'animal_count' => 2,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('livestock_inspections', [
        'owner_name' => $tenant->full_name,
        'livestock_type' => 'BEEF',
        'request_source' => 'TENANT',
        'tenant_id' => $tenant->id,
    ]);
});

it('supports collector management role reports and administrator record drilldowns', function () {
    $treasurer = User::where('usertype', User::ROLE_TREASURER)->firstOrFail();

    $this->actingAs($treasurer)
        ->get(route('treasurer.collectors'))
        ->assertOk()
        ->assertSee('Add Collector');

    $this->actingAs($treasurer)->post(route('treasurer.collectors.store'), [
        'firstname' => 'New',
        'lastname' => 'Collector',
        'username' => 'newcollector',
        'phone_num' => '09999999001',
        'email' => 'newcollector@example.test',
        'address' => 'Pandan, Antique',
        'designation' => 'Revenue Collector Clerk',
        'password' => 'password123',
    ])->assertRedirect();

    $this->actingAs($treasurer)
        ->get(route('treasurer.reports', ['report' => 'cash-ticket']))
        ->assertOk()
        ->assertSee('Cash Ticket');
    $this->actingAs($treasurer)->get(route('treasurer.stall-map'))->assertOk();
    $this->actingAs($treasurer)
        ->get(route('assignments.slip', CashTicketAssignment::firstOrFail()))
        ->assertOk()
        ->assertSee('REQUISITION AND ISSUE SLIP');
    $this->actingAs($treasurer)
        ->get(route('collections.report', CashTicketCollection::firstOrFail()))
        ->assertOk()
        ->assertSee('REPORT OF COLLECTION');

    $inspector = User::where('usertype', User::ROLE_INSPECTOR)->firstOrFail();
    $this->actingAs($inspector)
        ->get(route('inspector.reports', ['report' => 'inspection', 'livestock' => 'POULTRY']))
        ->assertOk()
        ->assertSee('Livestock');

    $administrator = User::where('usertype', User::ROLE_ADMINISTRATOR)->firstOrFail();
    foreach ([
        ['treasurer', null],
        ['treasurer', 'stall-rental'],
        ['clerk', 'cash-ticket'],
        ['inspector', 'inspection'],
        ['tenant', 'payment'],
    ] as [$role, $area]) {
        $this->actingAs($administrator)
            ->get(route('administrator.records', array_filter(compact('role', 'area'))))
            ->assertOk();
    }
});
