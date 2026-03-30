<?php

namespace Database\Seeders;

use App\Models\Horoscope;
use App\Models\ZodiacSign;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CreateDefaultHoroscopeSeeder extends Seeder
{
    public function run(): void
    {
        $zodiacs = ZodiacSign::all();

        $horoscopeData = [
            'daily' => [
                'date' => Carbon::today(),
                'title_suffix' => 'Daily Horoscope',
                'description' => 'Overall daily energy remains balanced and productive.',
                'love' => 'Small gestures will improve relationships.',
                'career' => 'Consistency will bring good results.',
                'health' => 'Maintain discipline in routine.',
                'finance' => 'Regular expenses remain manageable.',
            ],
            'weekly' => [
                'date' => Carbon::now()->startOfWeek(),
                'title_suffix' => 'Weekly Horoscope',
                'description' => 'This week focuses on growth and long-term planning.',
                'love' => 'Relationships grow stronger with patience.',
                'career' => 'Progress will be slow but steady.',
                'health' => 'Avoid overworking this week.',
                'finance' => 'Budget planning will be helpful.',
            ],
            'monthly' => [
                'date' => Carbon::now()->startOfMonth(),
                'title_suffix' => 'Monthly Horoscope',
                'description' => 'This month brings stability and clarity in life.',
                'love' => 'Emotional understanding will increase.',
                'career' => 'Career growth is likely this month.',
                'health' => 'Focus on long-term wellness.',
                'finance' => 'Good month for savings and investments.',
            ],
            'yearly' => [
                'date' => Carbon::now()->startOfYear(),
                'title_suffix' => 'Yearly Horoscope',
                'description' => 'This year opens doors to major transformation.',
                'love' => 'Strong emotional connections will develop.',
                'career' => 'Major career milestones are expected.',
                'health' => 'Overall health improves gradually.',
                'finance' => 'Financial growth and stability are indicated.',
            ],
        ];

        foreach ($zodiacs as $zodiac) {
            foreach ($horoscopeData as $type => $data) {

                Horoscope::create([
                    'zodiac_id'    => $zodiac->id,
                    'type'         => $type,
                    'date'         => $data['date']->toDateString(),
                    'title'        => $zodiac->name.' '.$data['title_suffix'],
                    'description'  => $data['description'],
                    'love'         => $data['love'],
                    'career'       => $data['career'],
                    'health'       => $data['health'],
                    'finance'      => $data['finance'],
                    'lucky_number' => rand(1, 9),
                    'lucky_color'  => ['Red','Blue','Green','Yellow','Purple'][array_rand(['Red','Blue','Green','Yellow','Purple'])],
                    'status'       => 1,
                    'created_by'   => 1,
                ]);
            }
        }
    }
}
