<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\CashTicketAssignment;
use App\Models\CashTicketCollection;
use App\Models\LivestockInspection;
use App\Models\Payment;
use App\Models\Stall;
use App\Models\StallApplication;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'firstname' => 'Erick',
                'middlename' => 'Louie',
                'lastname' => 'Lumanog',
                'username' => 'administrator',
                'designation' => 'Market Supervisor II',
                'email' => 'administrator@einspect.test',
                'usertype' => User::ROLE_ADMINISTRATOR,
                'phone_num' => '09123456781',
            ],
            [
                'firstname' => 'Romel',
                'middlename' => 'De',
                'lastname' => 'Juan',
                'username' => 'treasurer',
                'designation' => 'Municipal Treasurer',
                'email' => 'treasurer@einspect.test',
                'usertype' => User::ROLE_TREASURER,
                'phone_num' => '09123456782',
            ],
            [
                'firstname' => 'Arcanghel',
                'middlename' => 'S.',
                'lastname' => 'Fernando Jr.',
                'username' => 'clerk',
                'designation' => 'Revenue Collection Clerk I',
                'email' => 'clerk@einspect.test',
                'usertype' => User::ROLE_CLERK,
                'phone_num' => '09123456783',
            ],
            [
                'firstname' => 'Erwin',
                'middlename' => 'C.',
                'lastname' => 'Gregorio',
                'username' => 'inspector',
                'designation' => 'Rural Sanitary Inspector I',
                'email' => 'inspector@einspect.test',
                'usertype' => User::ROLE_INSPECTOR,
                'phone_num' => '09123456784',
            ],
            [
                'firstname' => 'Rex',
                'middlename' => 'P.',
                'lastname' => 'Bernesto',
                'username' => 'tenant',
                'designation' => 'Market Tenant',
                'email' => 'tenant@einspect.test',
                'usertype' => User::ROLE_TENANT,
                'phone_num' => '09123456785',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                [
                    ...$userData,
                    'address' => 'Pandan, Antique',
                    'phone_num' => $userData['phone_num'],
                    'status' => 'ACTIVE',
                    'password' => 'password',
                    'phone_verified_at' => now(),
                ]
            );
        }

        $sectionTotals = [
            'FISH' => 12,
            'POULTRY' => 17,
            'PORK' => 29,
            'BEEF' => 17,
            'MIXED' => 57,
        ];

        foreach ($sectionTotals as $section => $total) {
            foreach (range(1, $total) as $number) {
                Stall::updateOrCreate(
                    ['section' => $section, 'stall_number' => $number],
                    [
                        'monthly_rate' => in_array($section, ['FISH', 'POULTRY'], true) ? 750 : 900,
                        'status' => $number <= 3 ? 'OCCUPIED' : 'AVAILABLE',
                    ]
                );
            }
        }

        $administrator = User::where('usertype', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $treasurer = User::where('usertype', User::ROLE_TREASURER)->firstOrFail();
        $clerk = User::where('usertype', User::ROLE_CLERK)->firstOrFail();
        $inspector = User::where('usertype', User::ROLE_INSPECTOR)->firstOrFail();
        $tenant = User::where('usertype', User::ROLE_TENANT)->firstOrFail();
        $stall = Stall::where('section', 'FISH')->where('stall_number', 1)->firstOrFail();

        $application = StallApplication::updateOrCreate(
            ['application_number' => 'APP-DEMO-0001'],
            [
                'tenant_id' => $tenant->id,
                'stall_id' => $stall->id,
                'tin_number' => '123-456-789-000',
                'business_name' => 'Bernesto Fresh Fish',
                'business_category' => 'Fresh seafood retail',
                'business_address' => 'Pandan Public Market, Pandan, Antique',
                'preferred_section' => 'FISH',
                'preferred_stall_number' => 1,
                'status' => 'APPROVED',
                'reviewed_by' => $treasurer->id,
                'reviewed_at' => now()->subMonths(2),
            ]
        );

        StallApplication::updateOrCreate(
            ['application_number' => 'APP-DEMO-0002'],
            [
                'tenant_id' => $tenant->id,
                'tin_number' => '123-456-789-000',
                'business_name' => 'Bernesto Poultry Supply',
                'business_category' => 'Poultry',
                'business_address' => 'Pandan, Antique',
                'preferred_section' => 'POULTRY',
                'preferred_stall_number' => 7,
                'status' => 'PENDING',
            ]
        );

        foreach (range(0, 2) as $index) {
            $period = now()->subMonths(2 - $index)->startOfMonth();

            Payment::updateOrCreate(
                ['reference_number' => 'PAY-DEMO-000'.($index + 1)],
                [
                    'tenant_id' => $tenant->id,
                    'stall_application_id' => $application->id,
                    'amount' => 750,
                    'period_month' => $period,
                    'due_date' => $period->copy()->addDays(9),
                    'paid_at' => $index < 2 ? $period->copy()->addDays(4) : null,
                    'payment_method' => $index < 2 ? 'CASH' : null,
                    'status' => $index < 2 ? 'PAID' : 'PENDING',
                    'recorded_by' => $treasurer->id,
                ]
            );
        }

        foreach (['POULTRY', 'PORK', 'BEEF'] as $index => $type) {
            LivestockInspection::updateOrCreate(
                ['request_number' => 'INSP-DEMO-000'.($index + 1)],
                [
                    'tenant_id' => $tenant->id,
                    'inspector_id' => $index === 0 ? $inspector->id : null,
                    'livestock_type' => $type,
                    'owner_name' => $tenant->full_name,
                    'address' => $tenant->address,
                    'contact_number' => $tenant->phone_num,
                    'scheduled_at' => now()->addDays($index + 1)->setTime(9, 0),
                    'status' => $index === 0 ? 'APPROVED' : 'PENDING',
                    'animal_count' => ($index + 1) * 4,
                ]
            );
        }

        foreach (array_keys($sectionTotals) as $index => $section) {
            CashTicketCollection::updateOrCreate(
                ['collection_number' => 'CT-DEMO-000'.($index + 1)],
                [
                    'collector_id' => $clerk->id,
                    'recorded_by' => $clerk->id,
                    'stall_section' => $section,
                    'ticket_quantity' => 20 + $index,
                    'amount' => 2000 + ($index * 500),
                    'collection_date' => now()->subDays($index),
                    'status' => 'SUBMITTED',
                ]
            );
        }

        CashTicketAssignment::updateOrCreate(
            ['assignment_number' => 'CTA-DEMO-0001'],
            [
                'collector_id' => $clerk->id,
                'assigned_by' => $treasurer->id,
                'stall_section' => 'FISH',
                'ticket_start' => 1,
                'ticket_end' => 20,
                'ticket_quantity' => 20,
                'assigned_date' => now()->toDateString(),
                'status' => 'ASSIGNED',
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'Public Market Health and Sanitation Advisory'],
            [
                'author_id' => $treasurer->id,
                'category' => 'MARKET ADVISORY',
                'content' => 'All vendors, stallholders, and visitors are reminded to observe proper waste disposal, regular handwashing, and daily stall sanitation throughout the public market.',
                'published_at' => now()->subDay(),
                'is_published' => true,
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'Stall Rental Applications Are Open'],
            [
                'author_id' => $administrator->id,
                'category' => 'STALL RENTAL',
                'content' => 'Available stalls may now be viewed through the E-Inspect tenant portal. Submit complete business information for review by the market administration.',
                'published_at' => now()->subDays(2),
                'is_published' => true,
            ]
        );

        foreach ([
            'market_name' => 'Pandan Public Market',
            'market_address' => 'Pandan, Antique',
            'market_email' => 'pandanpublicmarket@example.test',
            'market_phone' => '+63 900 000 0000',
            'cash_ticket_value' => '100',
            'inspection_lead_days' => '1',
        ] as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'GENERAL']
            );
        }
    }
}
