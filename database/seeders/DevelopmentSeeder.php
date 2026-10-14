<?php

namespace Database\Seeders;

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Models\Course;
use App\Models\Period;
use Illuminate\Database\Seeder;

/**
 * Sample data for development: Jae's semester 1 music schedule, a few songs per grade, and
 * rosters full of Care Bears. Seventh grade is all villains. Existing classes (and everything
 * in them) are replaced, so it can be re-run to reset the demo.
 */
class DevelopmentSeeder extends Seeder
{
    /** @var list<array{0: string, 1: string}> */
    public const array CARE_BEARS = [
        ['Tenderheart', 'Bear'], ['Cheer', 'Bear'], ['Grumpy', 'Bear'], ['Funshine', 'Bear'],
        ['Good Luck', 'Bear'], ['Love-a-Lot', 'Bear'], ['Birthday', 'Bear'], ['Bedtime', 'Bear'],
        ['Wish', 'Bear'], ['Friend', 'Bear'], ['Share', 'Bear'], ['Champ', 'Bear'],
        ['Secret', 'Bear'], ['Harmony', 'Bear'], ['Surprise', 'Bear'], ['Grams', 'Bear'],
        ['Baby Hugs', 'Bear'], ['Baby Tugs', 'Bear'], ['Thanks-a-Lot', 'Bear'], ['Daydream', 'Bear'],
        ['Do-Your-Best', 'Bear'], ['Best Friend', 'Bear'], ['Hopeful Heart', 'Bear'], ['Laugh-a-Lot', 'Bear'],
        ['True Heart', 'Bear'], ['Always There', 'Bear'], ['Wonderheart', 'Bear'], ['Togetherness', 'Bear'],
        ['Oopsy', 'Bear'],
        ['Brave Heart', 'Lion'], ['Lotsa Heart', 'Elephant'], ['Bright Heart', 'Raccoon'], ['Cozy Heart', 'Penguin'],
        ['Gentle Heart', 'Lamb'], ['Loyal Heart', 'Dog'], ['Playful Heart', 'Monkey'], ['Proud Heart', 'Cat'],
        ['Swift Heart', 'Rabbit'], ['Treat Heart', 'Pig'], ['Noble Heart', 'Horse'],
    ];

    /**
     * Frostbite, Shreeky, and Beastly only have one name in the shows, so their surnames are made up.
     *
     * @var list<array{0: string, 1: string}>
     */
    public const array VILLAINS = [
        ['No', 'Heart'], ['Dark', 'Heart'], ['Professor', 'Coldheart'], ['Auntie', 'Freeze'],
        ['Frostbite', 'Freeze'], ['Shreeky', 'Heart'], ['Beastly', 'Beast'], ['Bluster', 'Bear'],
    ];

    /**
     * Each class and when it meets. Rooms for grades with a single class aren't on the schedule, so they're made up.
     *
     * @var list<array{grade: Grade, homeroom: string, color: Color, meetings: list<array{0: Day, 1: string}>}>
     */
    public const array SCHEDULE = [
        ['grade' => Grade::Kindergarten, 'homeroom' => '110', 'color' => Color::Black, 'meetings' => [[Day::Tuesday, '3rd Period'], [Day::Friday, '3rd Period']]],
        ['grade' => Grade::First, 'homeroom' => '226', 'color' => Color::Orange, 'meetings' => [[Day::Monday, '3rd Period'], [Day::Thursday, '3rd Period']]],
        ['grade' => Grade::First, 'homeroom' => '228', 'color' => Color::Red, 'meetings' => [[Day::Wednesday, '3rd Period']]],
        ['grade' => Grade::Second, 'homeroom' => '210', 'color' => Color::Yellow, 'meetings' => [[Day::Tuesday, '5th Period'], [Day::Friday, '5th Period']]],
        ['grade' => Grade::Third, 'homeroom' => '221', 'color' => Color::Green, 'meetings' => [[Day::Monday, '5th Period']]],
        ['grade' => Grade::Third, 'homeroom' => '224', 'color' => Color::Blue, 'meetings' => [[Day::Wednesday, '5th Period'], [Day::Thursday, '5th Period']]],
        ['grade' => Grade::Fourth, 'homeroom' => '214', 'color' => Color::Purple, 'meetings' => [[Day::Tuesday, '1st Period'], [Day::Friday, '1st Period']]],
        ['grade' => Grade::Fifth, 'homeroom' => '202', 'color' => Color::Red, 'meetings' => [[Day::Wednesday, '1st Period']]],
        ['grade' => Grade::Fifth, 'homeroom' => '203', 'color' => Color::Orange, 'meetings' => [[Day::Monday, '1st Period'], [Day::Thursday, '1st Period']]],
        ['grade' => Grade::Sixth, 'homeroom' => '301', 'color' => Color::Yellow, 'meetings' => [[Day::Friday, '6th Period']]],
        ['grade' => Grade::Sixth, 'homeroom' => '305', 'color' => Color::Green, 'meetings' => [[Day::Tuesday, '6th Period']]],
        ['grade' => Grade::Seventh, 'homeroom' => '307', 'color' => Color::Blue, 'meetings' => [[Day::Monday, '6th Period'], [Day::Thursday, '6th Period']]],
        ['grade' => Grade::Eighth, 'homeroom' => '308', 'color' => Color::Purple, 'meetings' => [[Day::Wednesday, '6th Period']]],
    ];

    /**
     * Songs in the order students work through them, by grade.
     *
     * @return list<string>
     */
    public static function songsFor(Grade $grade): array
    {
        return match ($grade) {
            Grade::Kindergarten => ['Twinkle Twinkle Little Star', 'Rain Rain Go Away', 'Bingo'],
            Grade::First => ['Row, Row, Row Your Boat', 'Lucy Locket', 'Bingo', 'Twinkle Twinkle Little Star'],
            Grade::Second => ['Bow Wow Wow', 'Hot Cross Buns', 'Lucy Locket', 'Doggie Doggie'],
            Grade::Third => ['Hot Cross Buns', 'Gently Sleep', 'Merrily We Roll Along', 'Old MacDonald'],
            Grade::Fourth => ['Merrily We Roll Along', 'Old MacDonald', 'Twinkle Twinkle Little Star', 'Ode to Joy'],
            Grade::Fifth => ['Ode to Joy', 'When the Saints Go Marching In', 'Amazing Grace', 'Au Clair de la Lune'],
            Grade::Sixth, Grade::Eighth => ['Ode to Joy', 'When the Saints Go Marching In', 'Amazing Grace', 'Lean on Me'],
            Grade::Seventh => ['In the Hall of the Mountain King', 'Night on Bald Mountain', 'Danse Macabre', 'Toccata and Fugue in D Minor'],
        };
    }

    public function run(): void
    {
        Course::query()->delete();

        $periods = Period::query()->pluck('id', 'name');
        $goodGuyClasses = collect(self::SCHEDULE)->where('grade', '!=', Grade::Seventh)->count();
        $goodGuys = collect(self::CARE_BEARS)->split($goodGuyClasses);
        $goodGuyGroup = 0;

        foreach (self::SCHEDULE as $class) {
            $course = Course::create(collect($class)->only(['grade', 'color', 'homeroom'])->all());

            foreach ($class['meetings'] as [$day, $period]) {
                $course->meetings()->create(['day' => $day, 'period_id' => $periods[$period]]);
            }

            foreach (self::songsFor($course->grade) as $position => $name) {
                $course->songs()->create(['name' => $name, 'position' => $position]);
            }

            $roster = $course->grade === Grade::Seventh ? self::VILLAINS : $goodGuys[$goodGuyGroup++]->values()->all();

            foreach ($roster as [$firstName, $lastName]) {
                $course->students()->create(['first_name' => $firstName, 'last_name' => $lastName]);
            }
        }
    }
}
