<?php

class LogisticsAnalytics
{
    public function capabilities()
    {
        return [
            [
                'number' => 18,
                'title' => 'Provider Commission Logic',
                'summary' => 'Calculates provider cash collection, platform commission, tax, and transfer due for each finished service.',
                'icon' => 'fa-scale-balanced'
            ],
            [
                'number' => 21,
                'title' => 'Batch Order Consolidation',
                'summary' => 'Groups orders by zone, delivery window, and vendor readiness to reduce route waste.',
                'icon' => 'fa-boxes-stacked'
            ],
            [
                'number' => 27,
                'title' => 'Sitter-to-Owner Incident Reporting',
                'summary' => 'Keeps incident updates visible with owner notification status and next action.',
                'icon' => 'fa-triangle-exclamation'
            ],
            [
                'number' => 30,
                'title' => 'Behavioral Profile Sharing',
                'summary' => 'Shares pet behavior notes with approved providers before bookings start.',
                'icon' => 'fa-share-nodes'
            ],
            [
                'number' => 32,
                'title' => 'Provider Income Analytics',
                'summary' => 'Tracks provider income, source mix, commissions, and payout readiness.',
                'icon' => 'fa-chart-line'
            ]
        ];
    }

    public function sections()
    {
        return [
            ['id' => 'provider-payments', 'label' => 'Provider payments', 'icon' => 'fa-money-bill-transfer'],
            ['id' => 'service-bookings', 'label' => 'Service bookings', 'icon' => 'fa-layer-group'],
            ['id' => 'completion-reporting', 'label' => 'Completion reports', 'icon' => 'fa-clipboard-list'],
            ['id' => 'behavior-tracking', 'label' => 'Behavior tracking', 'icon' => 'fa-paw'],
            ['id' => 'income-analytics', 'label' => 'Income analytics', 'icon' => 'fa-sack-dollar']
        ];
    }

    public function vendorCommissionRows()
    {
        return [
            [
                'vendor' => 'Happy Paws Supplies',
                'type' => 'Marketplace vendor',
                'orders' => 42,
                'gross_revenue' => 18450,
                'refunds' => 350,
                'logistics_cost' => 920,
                'commission_rate' => 0.12,
                'sla_score' => 96,
                'status' => 'Ready for payout',
                'trend' => '+8%'
            ],
            [
                'vendor' => 'City Vet Pharmacy',
                'type' => 'Vet-approved products',
                'orders' => 31,
                'gross_revenue' => 13200,
                'refunds' => 0,
                'logistics_cost' => 610,
                'commission_rate' => 0.10,
                'sla_score' => 92,
                'status' => 'Invoice review',
                'trend' => '+5%'
            ],
            [
                'vendor' => 'Walk & Wash Co.',
                'type' => 'Service provider',
                'orders' => 24,
                'gross_revenue' => 9750,
                'refunds' => 250,
                'logistics_cost' => 480,
                'commission_rate' => 0.15,
                'sla_score' => 88,
                'status' => 'Pending incidents',
                'trend' => '-2%'
            ],
            [
                'vendor' => 'PetCare Express',
                'type' => 'Delivery partner',
                'orders' => 57,
                'gross_revenue' => 22100,
                'refunds' => 410,
                'logistics_cost' => 1280,
                'commission_rate' => 0.08,
                'sla_score' => 97,
                'status' => 'Ready for payout',
                'trend' => '+11%'
            ]
        ];
    }

    public function batchOrders()
    {
        return [
            [
                'batch_id' => 'BCH-2041',
                'zone' => 'New Cairo / Rehab',
                'delivery_window' => 'Today, 4:00 PM - 7:00 PM',
                'vendor_count' => 3,
                'order_count' => 9,
                'subtotal' => 9650,
                'distance_saved_km' => 18,
                'status' => 'Packing',
                'route_note' => 'Two vendors are ready. Pharmacy items close at 3:30 PM.',
                'orders' => ['ORD-1182', 'ORD-1185', 'ORD-1191']
            ],
            [
                'batch_id' => 'BCH-2042',
                'zone' => 'Maadi / Zahraa',
                'delivery_window' => 'Tomorrow, 11:00 AM - 2:00 PM',
                'vendor_count' => 4,
                'order_count' => 12,
                'subtotal' => 12840,
                'distance_saved_km' => 24,
                'status' => 'Route locked',
                'route_note' => 'Route is optimized around sitter pickup and grooming drop-off.',
                'orders' => ['ORD-1202', 'ORD-1206', 'ORD-1210']
            ],
            [
                'batch_id' => 'BCH-2043',
                'zone' => 'Sheikh Zayed',
                'delivery_window' => 'Tomorrow, 5:00 PM - 8:00 PM',
                'vendor_count' => 2,
                'order_count' => 6,
                'subtotal' => 7320,
                'distance_saved_km' => 11,
                'status' => 'Needs vendor confirmation',
                'route_note' => 'One vendor still needs to confirm stock before consolidation.',
                'orders' => ['ORD-1217', 'ORD-1221', 'ORD-1224']
            ]
        ];
    }

    public function incidents()
    {
        return [
            [
                'incident_id' => 'INC-732',
                'pet' => 'Milo',
                'owner' => 'Nour Adel',
                'sitter' => 'Sara Care',
                'type' => 'Meal refusal',
                'severity' => 'Medium',
                'reported_at' => 'Today, 10:20 AM',
                'owner_notified' => true,
                'response_time' => '12 min',
                'status' => 'Owner reviewing',
                'next_action' => 'Owner received photos and hydration notes.'
            ],
            [
                'incident_id' => 'INC-728',
                'pet' => 'Luna',
                'owner' => 'Karim Samy',
                'sitter' => 'Walk & Wash Co.',
                'type' => 'Leash anxiety',
                'severity' => 'Low',
                'reported_at' => 'Yesterday, 6:45 PM',
                'owner_notified' => true,
                'response_time' => '8 min',
                'status' => 'Resolved',
                'next_action' => 'Behavior profile updated for future walks.'
            ],
            [
                'incident_id' => 'INC-719',
                'pet' => 'Bella',
                'owner' => 'Mariam Hassan',
                'sitter' => 'Home Paw Visits',
                'type' => 'Minor scratch',
                'severity' => 'High',
                'reported_at' => 'May 7, 2026, 2:10 PM',
                'owner_notified' => true,
                'response_time' => '5 min',
                'status' => 'Vet follow-up',
                'next_action' => 'Vet appointment request attached to the case.'
            ]
        ];
    }

    public function behaviorProfiles()
    {
        return [
            [
                'pet' => 'Luna',
                'species' => 'Dog',
                'owner' => 'Karim Samy',
                'shared_with' => 'Walk & Wash Co.',
                'share_status' => 'Shared',
                'last_update' => 'May 8, 2026',
                'signals' => ['Noise sensitive', 'Treat motivated', 'Slow introductions'],
                'provider_note' => 'Avoid busy streets during the first ten minutes of each walk.'
            ],
            [
                'pet' => 'Milo',
                'species' => 'Cat',
                'owner' => 'Nour Adel',
                'shared_with' => 'Sara Care',
                'share_status' => 'Owner approved',
                'last_update' => 'May 8, 2026',
                'signals' => ['Hides at first', 'Wet food routine', 'No belly touch'],
                'provider_note' => 'Let Milo approach first and keep feeding time consistent.'
            ],
            [
                'pet' => 'Bella',
                'species' => 'Dog',
                'owner' => 'Mariam Hassan',
                'shared_with' => 'Home Paw Visits',
                'share_status' => 'Review needed',
                'last_update' => 'May 7, 2026',
                'signals' => ['Protective', 'Crate trained', 'Morning medication'],
                'provider_note' => 'Provider must confirm medication reminder before accepting booking.'
            ]
        ];
    }

    public function incomeAnalytics()
    {
        return [
            'monthly' => [
                ['month' => 'Jan', 'income' => 18200, 'commission' => 2260],
                ['month' => 'Feb', 'income' => 21650, 'commission' => 2710],
                ['month' => 'Mar', 'income' => 24800, 'commission' => 3090],
                ['month' => 'Apr', 'income' => 29350, 'commission' => 3680],
                ['month' => 'May', 'income' => 26840, 'commission' => 3310]
            ],
            'sources' => [
                ['label' => 'Marketplace product payouts', 'amount' => 48200, 'color' => 'teal'],
                ['label' => 'Sitting and walking bookings', 'amount' => 31650, 'color' => 'green'],
                ['label' => 'Grooming service bookings', 'amount' => 18400, 'color' => 'sky'],
                ['label' => 'Owner tips and bonuses', 'amount' => 7250, 'color' => 'gold']
            ],
            'providers' => [
                ['provider' => 'Sara Care', 'service' => 'Pet sitting', 'bookings' => 18, 'income' => 14200, 'rating' => 4.9, 'payout_status' => 'Ready'],
                ['provider' => 'Walk & Wash Co.', 'service' => 'Walking + grooming', 'bookings' => 24, 'income' => 19750, 'rating' => 4.7, 'payout_status' => 'Review'],
                ['provider' => 'Home Paw Visits', 'service' => 'Home visits', 'bookings' => 13, 'income' => 9300, 'rating' => 4.8, 'payout_status' => 'Ready']
            ]
        ];
    }
}
