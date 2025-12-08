<?php

// namespace Database\Factories;

// use App\Models\Institution;
// use Illuminate\Database\Eloquent\Factories\Factory;

// /**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Institution>
//  */
// class InstitutionFactory extends Factory
// {
//     protected $model = Institution::class;

//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         $institutions = [
//             ['ar' => 'جامعة الملك سعود', 'en' => 'King Saud University'],
//             ['ar' => 'جامعة الملك فهد للبترول والمعادن', 'en' => 'King Fahd University of Petroleum and Minerals'],
//             ['ar' => 'جامعة الملك عبدالعزيز', 'en' => 'King Abdulaziz University'],
//             ['ar' => 'جامعة الإمام محمد بن سعود الإسلامية', 'en' => 'Imam Muhammad Ibn Saud Islamic University'],
//             ['ar' => 'جامعة أم القرى', 'en' => 'Umm Al-Qura University'],
//             ['ar' => 'جامعة الملك فيصل', 'en' => 'King Faisal University'],
//             ['ar' => 'جامعة الأميرة نورة بنت عبدالرحمن', 'en' => 'Princess Nourah bint Abdulrahman University'],
//             ['ar' => 'جامعة الطائف', 'en' => 'Taif University'],
//             ['ar' => 'جامعة جازان', 'en' => 'Jazan University'],
//             ['ar' => 'جامعة حائل', 'en' => 'University of Hail'],
//             ['ar' => 'كلية التقنية الصحية', 'en' => 'Health Technical College'],
//             ['ar' => 'المعهد الصحي', 'en' => 'Health Institute'],
//         ];

//         $institution = fake()->randomElement($institutions);

//         return [
//             'name' => json_encode([
//                 'ar' => $institution['ar'],
//                 'en' => $institution['en'],
//             ]),
//         ];
//     }
// }
