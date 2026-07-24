<?php

use App\Models\Announcement;
use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\LivestockInspection;
use App\Models\MarketMessage;
use App\Models\Payment;
use App\Models\StallApplication;
use App\Models\User;
use App\Models\VerificationCode;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('shows the wireframe homepage and opens the five-role login page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('PANDAN PUBLIC MARKET')
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
        ->assertSee('Create an Account');
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
    $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();

    $this->actingAs($tenant)
        ->post(route('tenant.applications.store'), [
            'business_name' => 'Test Market Store',
            'business_category' => 'Dry goods',
            'business_address' => 'Pandan, Antique',
            'preferred_section' => 'MIXED',
            'preferred_stall_number' => 12,
            'business_owner' => $tenant->full_name,
            'contact_number' => $tenant->phone_num,
        ])
        ->assertRedirect();

    expect(
        StallApplication::where('business_name', 'Test Market Store')->exists()
    )->toBeTrue();
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
        'documents' => [UploadedFile::fake()->create('barangay-permit.pdf', 300, 'application/pdf')],
    ])->assertRedirect();

    $application = StallApplication::where('business_owner', 'Public Applicant')->firstOrFail();
    expect($application->tenant_id)->toBeNull()
        ->and($application->request_source)->toBe('PUBLIC')
        ->and($application->documents)->toHaveCount(1);
    Storage::disk('public')->assertExists($application->documents->first()->path);
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
