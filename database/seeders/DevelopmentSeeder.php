<?php

namespace Database\Seeders;

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Models\Course;
use App\Models\Period;
use Illuminate\Database\Seeder;

/**
 * Sample data for development: one class per grade with two meeting times, a few songs, and
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

        $periods = Period::query()->ordered()->get();
        $dayPairs = [[Day::Monday, Day::Wednesday], [Day::Tuesday, Day::Thursday]];
        $goodGuys = collect(self::CARE_BEARS)->chunk((int) ceil(count(self::CARE_BEARS) / 8))->values();
        $goodGuyGroup = 0;

        foreach (Grade::cases() as $index => $grade) {
            $course = Course::create([
                'grade' => $grade,
                'color' => Color::cases()[$index % count(Color::cases())],
                'homeroom' => (string) (101 + $index),
            ]);

            // Fill the calendar without overlaps: two classes per period, Mon/Wed then Tue/Thu.
            $period = $periods[intdiv($index, 2) % $periods->count()];
            foreach ($dayPairs[$index % 2] as $day) {
                $course->meetings()->create(['day' => $day, 'period_id' => $period->id]);
            }

            foreach (self::songsFor($grade) as $position => $name) {
                $course->songs()->create(['name' => $name, 'position' => $position]);
            }

            $roster = $grade === Grade::Seventh ? self::VILLAINS : $goodGuys[$goodGuyGroup++]->values()->all();

            foreach ($roster as [$firstName, $lastName]) {
                $course->students()->create(['first_name' => $firstName, 'last_name' => $lastName]);
            }
        }
    }
}
