<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        Schema::disableForeignKeyConstraints();
        Service::truncate();
        Schema::enableForeignKeyConstraints();

        $categoryIds = Category::pluck('id', 'name');

        $services = [
            // 1. Brides & Party Makeup - BRIDES BY ZEHRA ABBAS
            [
                'name' => 'Baraat Makeup by Zehra Abbas',
                'description' => 'Complete bridal makeup for Baraat ceremony by Zehra Abbas',
                'rate' => 85000,
                'barcode' => 'ZA-BRIDAL-001',
                'status' => 1
            ],
            [
                'name' => 'Valima Makeup by Zehra Abbas',
                'description' => 'Complete bridal makeup for Valima ceremony by Zehra Abbas',
                'rate' => 85000,
                'barcode' => 'ZA-BRIDAL-002',
                'status' => 1
            ],
            [
                'name' => 'Baraat + Valima Package by Zehra Abbas',
                'description' => 'Complete bridal makeup package for both Baraat and Valima by Zehra Abbas',
                'rate' => 165000,
                'barcode' => 'ZA-BRIDAL-003',
                'status' => 1
            ],
            [
                'name' => 'Engagement/Nikkah/Mehndi by Zehra Abbas',
                'description' => 'Bridal makeup for Engagement, Nikkah or Mehndi ceremony by Zehra Abbas',
                'rate' => 55000,
                'barcode' => 'ZA-BRIDAL-004',
                'status' => 1
            ],
            [
                'name' => 'Party Makeup by Zehra Abbas',
                'description' => 'Party makeup by Zehra Abbas',
                'rate' => 25000,
                'barcode' => 'ZA-PARTY-001',
                'status' => 1
            ],

            // BRIDES BY TEAM ZA - Level 1
            [
                'name' => 'Baraat/Valima by Team ZA (Level 1)',
                'description' => 'Bridal makeup for Baraat or Valima by Team ZA - Level 1',
                'rate' => 50000,
                'barcode' => 'ZA-TEAM-001',
                'status' => 1
            ],
            [
                'name' => 'Engagement/Nikkah by Team ZA (Level 1)',
                'description' => 'Bridal makeup for Engagement or Nikkah by Team ZA - Level 1',
                'rate' => 40000,
                'barcode' => 'ZA-TEAM-002',
                'status' => 1
            ],
            [
                'name' => 'Mehndi by Team ZA (Level 1)',
                'description' => 'Bridal makeup for Mehndi by Team ZA - Level 1',
                'rate' => 38000,
                'barcode' => 'ZA-TEAM-003',
                'status' => 1
            ],

            // BRIDES BY TEAM ZA - Level 2
            [
                'name' => 'Baraat/Valima by Team ZA (Level 2)',
                'description' => 'Bridal makeup for Baraat or Valima by Team ZA - Level 2',
                'rate' => 40000,
                'barcode' => 'ZA-TEAM-004',
                'status' => 1
            ],
            [
                'name' => 'Engagement/Nikkah by Team ZA (Level 2)',
                'description' => 'Bridal makeup for Engagement or Nikkah by Team ZA - Level 2',
                'rate' => 35000,
                'barcode' => 'ZA-TEAM-005',
                'status' => 1
            ],
            [
                'name' => 'Mehndi by Team ZA (Level 2)',
                'description' => 'Bridal makeup for Mehndi by Team ZA - Level 2',
                'rate' => 30000,
                'barcode' => 'ZA-TEAM-006',
                'status' => 1
            ],

            // BRIDES BY TEAM ZA - Level 3
            [
                'name' => 'Baraat/Valima by Team ZA (Level 3)',
                'description' => 'Bridal makeup for Baraat or Valima by Team ZA - Level 3',
                'rate' => 28000,
                'barcode' => 'ZA-TEAM-007',
                'status' => 1
            ],
            [
                'name' => 'Engagement/Nikkah by Team ZA (Level 3)',
                'description' => 'Bridal makeup for Engagement or Nikkah by Team ZA - Level 3',
                'rate' => 24000,
                'barcode' => 'ZA-TEAM-008',
                'status' => 1
            ],
            [
                'name' => 'Mehndi by Team ZA (Level 3)',
                'description' => 'Bridal makeup for Mehndi by Team ZA - Level 3',
                'rate' => 22000,
                'barcode' => 'ZA-TEAM-009',
                'status' => 1
            ],
            // PARTY MAKEUPS BY TEAM ZA
            [
                'name' => 'Soft Party (Makeup + Lashes + Open Hairdo)',
                'description' => 'Soft party makeup with lashes and open hairdo',
                'rate' => 9000,
                'barcode' => 'ZA-SOFT-001',
                'status' => 1
            ],
            [
                'name' => 'Soft Party (Makeup + Lashes + Nail Color + Updo)',
                'description' => 'Soft party makeup with lashes, nail color and updo',
                'rate' => 12000,
                'barcode' => 'ZA-SOFT-002',
                'status' => 1
            ],
            [
                'name' => 'Glittery Party (Makeup + Lashes + Open Hairdo)',
                'description' => 'Glittery party makeup with lashes and open hairdo',
                'rate' => 12000,
                'barcode' => 'ZA-GLITTER-001',
                'status' => 1
            ],
            [
                'name' => 'Glittery Party (Makeup + Lashes + Nail Color + Updo)',
                'description' => 'Glittery party makeup with lashes, nail color and updo',
                'rate' => 15000,
                'barcode' => 'ZA-GLITTER-002',
                'status' => 1
            ],
            [
                'name' => 'Smokey Party (Makeup + Lashes + Open Hairdo)',
                'description' => 'Smokey party makeup with lashes and open hairdo',
                'rate' => 12000,
                'barcode' => 'ZA-SMOKEY-001',
                'status' => 1
            ],
            [
                'name' => 'Smokey Party (Makeup + Lashes + Nail Color + Updo)',
                'description' => 'Smokey party makeup with lashes, nail color and updo',
                'rate' => 15000,
                'barcode' => 'ZA-SMOKEY-002',
                'status' => 1
            ],
            [
                'name' => 'Eye Makeup',
                'description' => 'Professional eye makeup service',
                'rate' => 5000,
                'barcode' => 'ZA-EYE-001',
                'status' => 1
            ],

            // EXTRAS
            [
                'name' => 'Morning Makeup (12:00pm to 2:30pm)',
                'description' => 'Additional charge for morning makeup ready timings',
                'rate' => 5000,
                'barcode' => 'ZA-EXTRA-001',
                'status' => 1
            ],
            [
                'name' => 'Specific Artist Party',
                'description' => 'Additional charge for specific artist request',
                'rate' => 5000,
                'barcode' => 'ZA-EXTRA-002',
                'status' => 1
            ],
            [
                'name' => 'Hair Extensions',
                'description' => 'Hair extensions service',
                'rate' => 5000,
                'barcode' => 'ZA-EXTRA-003',
                'status' => 1
            ],
            [
                'name' => 'Makeup Touch Up',
                'description' => 'Makeup touch up service',
                'rate' => 5000,
                'barcode' => 'ZA-EXTRA-004',
                'status' => 1
            ],
            [
                'name' => 'Red Lipstick',
                'description' => 'Red lipstick application service',
                'rate' => 5000,
                'barcode' => 'ZA-EXTRA-005',
                'status' => 1
            ],
            [
                'name' => 'Flowers',
                'description' => 'Flower arrangements for hair',
                'rate' => 3500, // Average of 2000-5000
                'barcode' => 'ZA-EXTRA-006',
                'status' => 1
            ],
            // 2. Bridal Spa Services
            [
                'name' => 'Diamond Bridal Package',
                'description' => 'Spa manicure/pedicure; full arms and full legs wax, underarms (fruit wax); Guinot facial, face polish; face wax',
                'rate' => 24000,
                'barcode' => 'ZA-SPA-001',
                'status' => 1
            ],
            [
                'name' => 'Luxury Bridal Package',
                'description' => 'Solution manicure/pedicure; full body fruit wax (without bikini); Thalgo facial; haircut or body scrubbing; hair treatment',
                'rate' => 37500,
                'barcode' => 'ZA-SPA-002',
                'status' => 1
            ],
            [
                'name' => 'ZA Signature Service',
                'description' => 'Hydra facial; Thalgo manicure; Thalgo pedicure; full body fruit wax (without bikini); body scrub & polish; face wax',
                'rate' => 48000,
                'barcode' => 'ZA-SPA-003',
                'status' => 1
            ],

            // 3. Hair & Color - HAIR
            [
                'name' => 'Hair Do',
                'description' => 'Professional hair styling service',
                'rate' => 4000, // Average of 3000-5000
                'barcode' => 'ZA-HAIR-001',
                'status' => 1
            ],
            [
                'name' => 'Baby Hair Do',
                'description' => 'Hair styling for babies and small children',
                'rate' => 2750, // Average of 2000-3500
                'barcode' => 'ZA-HAIR-002',
                'status' => 1
            ],
            [
                'name' => 'Hair Cut',
                'description' => 'Professional haircut service',
                'rate' => 4500,
                'barcode' => 'ZA-HAIR-003',
                'status' => 1
            ],
            [
                'name' => 'Haircut (Senior Stylist)',
                'description' => 'Haircut by senior stylist',
                'rate' => 6500,
                'barcode' => 'ZA-HAIR-004',
                'status' => 1
            ],
            [
                'name' => 'Bangs Cut',
                'description' => 'Bangs cutting service',
                'rate' => 2000,
                'barcode' => 'ZA-HAIR-005',
                'status' => 1
            ],
            [
                'name' => 'Baby Hair Cut',
                'description' => 'Haircut for babies and small children',
                'rate' => 3500,
                'barcode' => 'ZA-HAIR-006',
                'status' => 1
            ],
            [
                'name' => 'Trimming',
                'description' => 'Hair trimming service',
                'rate' => 2500,
                'barcode' => 'ZA-HAIR-007',
                'status' => 1
            ],
            [
                'name' => 'Blow Dry (Wash)',
                'description' => 'Blow dry with hair wash',
                'rate' => 3000,
                'barcode' => 'ZA-HAIR-008',
                'status' => 1
            ],
            [
                'name' => 'Blow Dry (Without Wash)',
                'description' => 'Blow dry without hair wash',
                'rate' => 2500,
                'barcode' => 'ZA-HAIR-009',
                'status' => 1
            ],
            [
                'name' => 'Normal Wash & Paddle Dry',
                'description' => 'Basic hair wash and paddle dry',
                'rate' => 1500,
                'barcode' => 'ZA-HAIR-010',
                'status' => 1
            ],

            // TEXTURE SMOOTHING
            [
                'name' => 'Keratin Treatment',
                'description' => 'Keratin hair smoothing treatment',
                'rate' => 22000,
                'barcode' => 'ZA-TEXTURE-001',
                'status' => 1
            ],
            [
                'name' => 'Botox Smoothing Therapy',
                'description' => 'Botox hair smoothing therapy treatment',
                'rate' => 20000,
                'barcode' => 'ZA-TEXTURE-002',
                'status' => 1
            ],
            [
                'name' => 'Xtenso Treatment',
                'description' => 'Xtenso hair straightening treatment',
                'rate' => 20000,
                'barcode' => 'ZA-TEXTURE-003',
                'status' => 1
            ],

            // COLOR
            [
                'name' => 'Roots Touchup (1 inch)',
                'description' => 'Hair roots touchup service for 1 inch',
                'rate' => 6000,
                'barcode' => 'ZA-COLOR-001',
                'status' => 1
            ],
            [
                'name' => 'Roots Touchup (Invoa Ammonia Free, 1 inch)',
                'description' => 'Ammonia-free hair roots touchup service for 1 inch',
                'rate' => 15000,
                'barcode' => 'ZA-COLOR-002',
                'status' => 1
            ],
            [
                'name' => 'Roots Touchup (Igora, 1 inch)',
                'description' => 'Igora hair roots touchup service for 1 inch',
                'rate' => 15000,
                'barcode' => 'ZA-COLOR-003',
                'status' => 1
            ],
            [
                'name' => 'One Color / Base Color',
                'description' => 'Single color or base color hair coloring',
                'rate' => 15000,
                'barcode' => 'ZA-COLOR-004',
                'status' => 1
            ],
            [
                'name' => 'One Color (Ammonia Free)',
                'description' => 'Ammonia-free single color hair coloring',
                'rate' => 25000,
                'barcode' => 'ZA-COLOR-005',
                'status' => 1
            ],
            [
                'name' => 'Fashion Color / Base Color',
                'description' => 'Fashion color or base color hair coloring',
                'rate' => 20000,
                'barcode' => 'ZA-COLOR-006',
                'status' => 1
            ],
            [
                'name' => 'Hair Glossing',
                'description' => 'Hair glossing treatment',
                'rate' => 15000,
                'barcode' => 'ZA-COLOR-007',
                'status' => 1
            ],
            [
                'name' => 'Balayage',
                'description' => 'Balayage hair coloring technique',
                'rate' => 25000,
                'barcode' => 'ZA-COLOR-008',
                'status' => 1
            ],
            [
                'name' => 'Foilage',
                'description' => 'Foilage hair coloring technique',
                'rate' => 25000,
                'barcode' => 'ZA-COLOR-009',
                'status' => 1
            ],
            [
                'name' => 'Hollywood Blend',
                'description' => 'Hollywood blend hair coloring technique',
                'rate' => 28000,
                'barcode' => 'ZA-COLOR-010',
                'status' => 1
            ],
            [
                'name' => 'No Bleach Balayage',
                'description' => 'No bleach balayage hair coloring technique',
                'rate' => 25000,
                'barcode' => 'ZA-COLOR-011',
                'status' => 1
            ],
            [
                'name' => 'Highlights / Lowlights',
                'description' => 'Hair highlights or lowlights service',
                'rate' => 22000,
                'barcode' => 'ZA-COLOR-012',
                'status' => 1
            ],
            [
                'name' => 'Hair Strand Test',
                'description' => 'Hair strand test before coloring',
                'rate' => 2000,
                'barcode' => 'ZA-COLOR-013',
                'status' => 1
            ],
            [
                'name' => 'Igora - Schwarzkopf (Additional)',
                'description' => 'Additional charge for Igora - Schwarzkopf products',
                'rate' => 5000,
                'barcode' => 'ZA-COLOR-014',
                'status' => 1
            ],

            // 4. Facials - Guinot
            [
                'name' => 'Detoxifying & Radiance Glow Facial (Guinot)',
                'description' => 'Guinot detoxifying and radiance glow facial',
                'rate' => 9000,
                'barcode' => 'ZA-FACIAL-001',
                'status' => 1
            ],
            [
                'name' => 'Oxygenating Facial (Guinot)',
                'description' => 'Guinot oxygenating facial treatment',
                'rate' => 10500,
                'barcode' => 'ZA-FACIAL-002',
                'status' => 1
            ],
            [
                'name' => 'Acne Treatment Facial (Guinot)',
                'description' => 'Guinot acne treatment facial',
                'rate' => 14000,
                'barcode' => 'ZA-FACIAL-003',
                'status' => 1
            ],
            [
                'name' => 'Lifting Facial (Guinot)',
                'description' => 'Guinot lifting facial treatment',
                'rate' => 14000,
                'barcode' => 'ZA-FACIAL-004',
                'status' => 1
            ],
            [
                'name' => 'Normal Cleansing (Guinot)',
                'description' => 'Guinot normal cleansing facial',
                'rate' => 8000,
                'barcode' => 'ZA-FACIAL-005',
                'status' => 1
            ],
            [
                'name' => 'Brightening Facial (Guinot)',
                'description' => 'Guinot brightening facial treatment',
                'rate' => 10500,
                'barcode' => 'ZA-FACIAL-006',
                'status' => 1
            ],

            // Thalgo Facials
            [
                'name' => 'Hyaluronic Lumiere Facial (Thalgo)',
                'description' => 'Thalgo hyaluronic lumiere facial treatment',
                'rate' => 13500,
                'barcode' => 'ZA-FACIAL-007',
                'status' => 1
            ],
            [
                'name' => 'Hyaluronic Peeling Treatment (Thalgo)',
                'description' => 'Thalgo hyaluronic peeling treatment',
                'rate' => 17000,
                'barcode' => 'ZA-FACIAL-008',
                'status' => 1
            ],
            [
                'name' => 'Normal Cleansing (Thalgo)',
                'description' => 'Thalgo normal cleansing facial',
                'rate' => 7000,
                'barcode' => 'ZA-FACIAL-009',
                'status' => 1
            ],

            // Janssen Facials
            [
                'name' => 'Deep Cleansing (Janssen)',
                'description' => 'Janssen deep cleansing facial',
                'rate' => 7000,
                'barcode' => 'ZA-FACIAL-010',
                'status' => 1
            ],
            [
                'name' => 'Acne Treatment Facial (Janssen)',
                'description' => 'Janssen acne treatment facial',
                'rate' => 9000,
                'barcode' => 'ZA-FACIAL-011',
                'status' => 1
            ],
            [
                'name' => 'Brightening Glow Facial (Janssen)',
                'description' => 'Janssen brightening glow facial',
                'rate' => 8500,
                'barcode' => 'ZA-FACIAL-012',
                'status' => 1
            ],
            [
                'name' => 'Youth Lift Facial (Janssen)',
                'description' => 'Janssen youth lift facial treatment',
                'rate' => 11500,
                'barcode' => 'ZA-FACIAL-013',
                'status' => 1
            ],

            // Add Ons for Facials
            [
                'name' => 'LED Light Dome',
                'description' => 'LED light dome facial add-on treatment',
                'rate' => 3000,
                'barcode' => 'ZA-ADDON-001',
                'status' => 1
            ],
            [
                'name' => 'Oxygen Dome',
                'description' => 'Oxygen dome facial add-on treatment',
                'rate' => 3000,
                'barcode' => 'ZA-ADDON-002',
                'status' => 1
            ],
            [
                'name' => 'Hydro Jelly Mask',
                'description' => 'Hydro jelly mask facial add-on',
                'rate' => 3000,
                'barcode' => 'ZA-ADDON-003',
                'status' => 1
            ],
            [
                'name' => 'High Frequency Machine',
                'description' => 'High frequency machine facial add-on',
                'rate' => 1500,
                'barcode' => 'ZA-ADDON-004',
                'status' => 1
            ],
            [
                'name' => 'Hyaluronic Mask',
                'description' => 'Hyaluronic mask facial add-on',
                'rate' => 3500,
                'barcode' => 'ZA-ADDON-005',
                'status' => 1
            ],
            [
                'name' => 'Face Polish',
                'description' => 'Face polish treatment',
                'rate' => 2000,
                'barcode' => 'ZA-ADDON-006',
                'status' => 1
            ],
            [
                'name' => 'Dermaplaning',
                'description' => 'Dermaplaning facial treatment',
                'rate' => 5000,
                'barcode' => 'ZA-ADDON-007',
                'status' => 1
            ],
            // 5. Hands & Feet Care
            [
                'name' => 'Under Arms Treatment',
                'description' => 'Complete under arms treatment including cleansing, exfoliation, and treating dark spots and ingrown hairs',
                'rate' => 9000,
                'barcode' => 'ZA-UNDERARM-001',
                'status' => 1
            ],

            // Manicure Services
            [
                'name' => 'Spa Manicure',
                'description' => 'Spa manicure treatment',
                'rate' => 3000,
                'barcode' => 'ZA-MANI-001',
                'status' => 1
            ],
            [
                'name' => 'Solution Manicure',
                'description' => 'Solution manicure treatment',
                'rate' => 4000,
                'barcode' => 'ZA-MANI-002',
                'status' => 1
            ],
            [
                'name' => 'Thalgo Marins Manicure',
                'description' => 'Thalgo Marins manicure treatment',
                'rate' => 5000,
                'barcode' => 'ZA-MANI-003',
                'status' => 1
            ],
            // Pedicure Services
            [
                'name' => 'Spa Pedicure',
                'description' => 'Spa pedicure treatment',
                'rate' => 3500,
                'barcode' => 'ZA-PEDI-001',
                'status' => 1
            ],
            [
                'name' => 'Solution Pedicure',
                'description' => 'Solution pedicure treatment',
                'rate' => 4500,
                'barcode' => 'ZA-PEDI-002',
                'status' => 1
            ],
            [
                'name' => 'Thalgo Marins Pedicure',
                'description' => 'Thalgo Marins pedicure treatment',
                'rate' => 5500,
                'barcode' => 'ZA-PEDI-003',
                'status' => 1
            ],
            [
                'name' => 'Footlogix Pedicure',
                'description' => 'Footlogix pedicure treatment',
                'rate' => 7000,
                'barcode' => 'ZA-PEDI-004',
                'status' => 1
            ],
            // Additional Hand & Feet Services
            [
                'name' => 'Paraffin Treatment (Hands)',
                'description' => 'Paraffin treatment for hands',
                'rate' => 2000,
                'barcode' => 'ZA-HANDS-001',
                'status' => 1
            ],
            [
                'name' => 'Paraffin Treatment (Feet)',
                'description' => 'Paraffin treatment for feet',
                'rate' => 2000,
                'barcode' => 'ZA-FEET-001',
                'status' => 1
            ],
            [
                'name' => 'Stone Massage (Hands)',
                'description' => 'Stone massage for hands',
                'rate' => 1500,
                'barcode' => 'ZA-HANDS-002',
                'status' => 1
            ],
            [
                'name' => 'Stone Massage (Feet)',
                'description' => 'Stone massage for feet',
                'rate' => 1500,
                'barcode' => 'ZA-FEET-002',
                'status' => 1
            ],
            [
                'name' => 'Polishing (Hands)',
                'description' => 'Hand polishing treatment',
                'rate' => 750,
                'barcode' => 'ZA-HANDS-003',
                'status' => 1
            ],
            [
                'name' => 'Polishing (Feet)',
                'description' => 'Feet polishing treatment',
                'rate' => 750,
                'barcode' => 'ZA-FEET-003',
                'status' => 1
            ],
            [
                'name' => 'Nail Color (Hands)',
                'description' => 'Nail color application for hands',
                'rate' => 500,
                'barcode' => 'ZA-HANDS-004',
                'status' => 1
            ],
            [
                'name' => 'Nail Color (Feet)',
                'description' => 'Nail color application for feet',
                'rate' => 500,
                'barcode' => 'ZA-FEET-004',
                'status' => 1
            ],
            [
                'name' => '786 Nail Color (Hands)',
                'description' => '786 nail color application for hands',
                'rate' => 500,
                'barcode' => 'ZA-HANDS-005',
                'status' => 1
            ],
            [
                'name' => '786 Nail Color (Feet)',
                'description' => '786 nail color application for feet',
                'rate' => 500,
                'barcode' => 'ZA-FEET-005',
                'status' => 1
            ],
            [
                'name' => 'French Nail (Hands)',
                'description' => 'French nail art for hands',
                'rate' => 1500,
                'barcode' => 'ZA-HANDS-006',
                'status' => 1
            ],
            [
                'name' => 'French Nail (Feet)',
                'description' => 'French nail art for feet',
                'rate' => 1500,
                'barcode' => 'ZA-FEET-006',
                'status' => 1
            ],
            [
                'name' => 'Fake Nail Application',
                'description' => 'Fake nail application service',
                'rate' => 1000,
                'barcode' => 'ZA-HANDS-007',
                'status' => 1
            ],

            // 6. Massage & Body Cleansing
            [
                'name' => 'Lava Shell Massage (60 min)',
                'description' => '60-minute lava shell massage therapy',
                'rate' => 18000,
                'barcode' => 'ZA-MASSAGE-001',
                'status' => 1
            ],
            [
                'name' => 'Stone Massage (60 min)',
                'description' => '60-minute hot stone massage therapy',
                'rate' => 12000,
                'barcode' => 'ZA-MASSAGE-002',
                'status' => 1
            ],
            [
                'name' => 'Lymphatic Maderotherapy Massage (60 min)',
                'description' => '60-minute lymphatic maderotherapy massage',
                'rate' => 10000,
                'barcode' => 'ZA-MASSAGE-003',
                'status' => 1
            ],
            [
                'name' => 'Deep Tissue Massage (60 min)',
                'description' => '60-minute deep tissue massage therapy',
                'rate' => 16000,
                'barcode' => 'ZA-MASSAGE-004',
                'status' => 1
            ],
            [
                'name' => 'Postpartum Bliss Massage (60 min)',
                'description' => '60-minute postpartum bliss massage therapy',
                'rate' => 15000,
                'barcode' => 'ZA-MASSAGE-005',
                'status' => 1
            ],
            [
                'name' => 'Aromatherapy Candle Massage (60 min)',
                'description' => '60-minute aromatherapy candle massage',
                'rate' => 14000,
                'barcode' => 'ZA-MASSAGE-006',
                'status' => 1
            ],
            [
                'name' => 'Relaxing Oil Massage (60 min)',
                'description' => '60-minute relaxing oil massage therapy',
                'rate' => 9500,
                'barcode' => 'ZA-MASSAGE-007',
                'status' => 1
            ],
            [
                'name' => 'Shoulder Massage (25 min)',
                'description' => '25-minute shoulder massage therapy',
                'rate' => 3000,
                'barcode' => 'ZA-MASSAGE-008',
                'status' => 1
            ],
            [
                'name' => 'Foot Massage (25 min)',
                'description' => '25-minute foot massage therapy',
                'rate' => 3000,
                'barcode' => 'ZA-MASSAGE-009',
                'status' => 1
            ],
            [
                'name' => 'Hair Oil Massage (30 min)',
                'description' => '30-minute hair oil massage',
                'rate' => 2500,
                'barcode' => 'ZA-MASSAGE-010',
                'status' => 1
            ],

            // Body Cleansing
            [
                'name' => 'Body Scrubbing',
                'description' => 'Full body scrubbing treatment',
                'rate' => 7000,
                'barcode' => 'ZA-BODY-001',
                'status' => 1
            ],
            [
                'name' => 'Body Polishing',
                'description' => 'Full body polishing treatment',
                'rate' => 7000,
                'barcode' => 'ZA-BODY-002',
                'status' => 1
            ],

            // 7. Wax & Threading
            [
                'name' => 'Full Body Wax (Honey)',
                'description' => 'Full body waxing with honey wax',
                'rate' => 6600,
                'barcode' => 'ZA-WAX-001',
                'status' => 1
            ],
            [
                'name' => 'Full Body Wax (Fruit)',
                'description' => 'Full body waxing with fruit wax',
                'rate' => 8500,
                'barcode' => 'ZA-WAX-002',
                'status' => 1
            ],
            [
                'name' => 'Full Body Wax (Roll On)',
                'description' => 'Full body waxing with roll-on wax',
                'rate' => 8000,
                'barcode' => 'ZA-WAX-003',
                'status' => 1
            ],
            [
                'name' => 'Half Leg Wax (Honey)',
                'description' => 'Half leg waxing with honey wax',
                'rate' => 1400,
                'barcode' => 'ZA-WAX-016',
                'status' => 1
            ],
            [
                'name' => 'Half Leg Wax (Fruit)',
                'description' => 'Half leg waxing with fruit wax',
                'rate' => 1500,
                'barcode' => 'ZA-WAX-017',
                'status' => 1
            ],
            [
                'name' => 'Half Leg Wax (Roll On)',
                'description' => 'Half leg waxing with roll-on wax',
                'rate' => 1700,
                'barcode' => 'ZA-WAX-018',
                'status' => 1
            ],
            [
                'name' => 'Full Leg Wax (Honey)',
                'description' => 'Full leg waxing with honey wax',
                'rate' => 1800,
                'barcode' => 'ZA-WAX-019',
                'status' => 1
            ],
            [
                'name' => 'Full Leg Wax (Fruit)',
                'description' => 'Full leg waxing with fruit wax',
                'rate' => 2500,
                'barcode' => 'ZA-WAX-020',
                'status' => 1
            ],
            [
                'name' => 'Full Leg Wax (Roll On)',
                'description' => 'Full leg waxing with roll-on wax',
                'rate' => 3500,
                'barcode' => 'ZA-WAX-021',
                'status' => 1
            ],
            [
                'name' => 'Front/Back Wax (Honey)',
                'description' => 'Front or back waxing with honey wax',
                'rate' => 1200,
                'barcode' => 'ZA-WAX-022',
                'status' => 1
            ],
            [
                'name' => 'Front/Back Wax (Fruit)',
                'description' => 'Front or back waxing with fruit wax',
                'rate' => 1750,
                'barcode' => 'ZA-WAX-023',
                'status' => 1
            ],
            // Partial Waxing Services
            [
                'name' => 'Half Arm Wax (Honey)',
                'description' => 'Half arm waxing with honey wax',
                'rate' => 700,
                'barcode' => 'ZA-WAX-004',
                'status' => 1
            ],
            [
                'name' => 'Half Arm Wax (Fruit)',
                'description' => 'Half arm waxing with fruit wax',
                'rate' => 1000,
                'barcode' => 'ZA-WAX-005',
                'status' => 1
            ],
            [
                'name' => 'Half Arm Wax (Roll On)',
                'description' => 'Half arm waxing with roll-on wax',
                'rate' => 1200,
                'barcode' => 'ZA-WAX-006',
                'status' => 1
            ],
            [
                'name' => 'Full Arm Wax (Honey)',
                'description' => 'Full arm waxing with honey wax',
                'rate' => 1200,
                'barcode' => 'ZA-WAX-007',
                'status' => 1
            ],
            [
                'name' => 'Full Arm Wax (Fruit)',
                'description' => 'Full arm waxing with fruit wax',
                'rate' => 1500,
                'barcode' => 'ZA-WAX-008',
                'status' => 1
            ],
            [
                'name' => 'Full Arm Wax (Roll On)',
                'description' => 'Full arm waxing with roll-on wax',
                'rate' => 1700,
                'barcode' => 'ZA-WAX-009',
                'status' => 1
            ],
            [
                'name' => 'Under Arms Wax (Honey)',
                'description' => 'Under arms waxing with honey wax',
                'rate' => 500,
                'barcode' => 'ZA-WAX-010',
                'status' => 1
            ],
            [
                'name' => 'Under Arms Wax (Fruit)',
                'description' => 'Under arms waxing with fruit wax',
                'rate' => 800,
                'barcode' => 'ZA-WAX-011',
                'status' => 1
            ],
            [
                'name' => 'Under Arms Wax (Roll On)',
                'description' => 'Under arms waxing with roll-on wax',
                'rate' => 1200,
                'barcode' => 'ZA-WAX-012',
                'status' => 1
            ],
            [
                'name' => 'Under Arms Wax (Lycon)',
                'description' => 'Under arms waxing with Lycon wax',
                'rate' => 3000,
                'barcode' => 'ZA-WAX-013',
                'status' => 1
            ],
            [
                'name' => 'Bikini Wax (Fruit)',
                'description' => 'Bikini area waxing with fruit wax',
                'rate' => 3000,
                'barcode' => 'ZA-WAX-014',
                'status' => 1
            ],
            [
                'name' => 'Bikini Wax (Lycon)',
                'description' => 'Bikini area waxing with Lycon wax',
                'rate' => 6000,
                'barcode' => 'ZA-WAX-015',
                'status' => 1
            ],

            // Threading Services
            [
                'name' => 'Eyebrows Threading',
                'description' => 'Eyebrow shaping with threading',
                'rate' => 500,
                'barcode' => 'ZA-THREAD-001',
                'status' => 1
            ],
            [
                'name' => 'Upper Lips Threading',
                'description' => 'Upper lip hair removal with threading',
                'rate' => 500,
                'barcode' => 'ZA-THREAD-002',
                'status' => 1
            ],
            [
                'name' => 'Full Face Threading',
                'description' => 'Full face hair removal with threading',
                'rate' => 2200,
                'barcode' => 'ZA-THREAD-003',
                'status' => 1
            ],
            [
                'name' => 'Eyebrows Waxing',
                'description' => 'Eyebrow shaping with wax',
                'rate' => 500,
                'barcode' => 'ZA-FACEWAX-001',
                'status' => 1
            ],
            [
                'name' => 'Upper Lips Waxing',
                'description' => 'Upper lip hair removal with wax',
                'rate' => 500,
                'barcode' => 'ZA-FACEWAX-002',
                'status' => 1
            ],
            [
                'name' => 'Full Face Waxing',
                'description' => 'Full face hair removal with wax',
                'rate' => 2500,
                'barcode' => 'ZA-FACEWAX-003',
                'status' => 1
            ],
            // 8. ZA Nails
            [
                'name' => 'Acrylic + Gel Nail Colour',
                'description' => 'Acrylic nails with gel nail color',
                'rate' => 11500,
                'barcode' => 'ZA-NAILS-001',
                'status' => 1
            ],
            [
                'name' => 'Ombré Nails',
                'description' => 'Ombré nail art design',
                'rate' => 12000,
                'barcode' => 'ZA-NAILS-002',
                'status' => 1
            ],
            [
                'name' => 'French Acrylic Application',
                'description' => 'French acrylic nail application',
                'rate' => 8500,
                'barcode' => 'ZA-NAILS-003',
                'status' => 1
            ],
            [
                'name' => 'Gel Nails + French Tips',
                'description' => 'Gel nails with French tip design',
                'rate' => 7500,
                'barcode' => 'ZA-NAILS-004',
                'status' => 1
            ],
            [
                'name' => 'Gel Nail Colour',
                'description' => 'Gel nail color application',
                'rate' => 4000,
                'barcode' => 'ZA-NAILS-005',
                'status' => 1
            ],
            [
                'name' => 'Acrylic Removal',
                'description' => 'Acrylic nail removal service',
                'rate' => 3000,
                'barcode' => 'ZA-NAILS-006',
                'status' => 1
            ],
            [
                'name' => 'Acrylic Refill',
                'description' => 'Acrylic nail refill service',
                'rate' => 4000,
                'barcode' => 'ZA-NAILS-007',
                'status' => 1
            ],
            [
                'name' => 'Gel Removal',
                'description' => 'Gel nail removal service',
                'rate' => 3000,
                'barcode' => 'ZA-NAILS-008',
                'status' => 1
            ],

            // Nail Add-ons
            [
                'name' => 'Cat Eye Nails',
                'description' => 'Cat eye nail art design',
                'rate' => 4000,
                'barcode' => 'ZA-NAILS-009',
                'status' => 1
            ],
            [
                'name' => 'Chrome Nails',
                'description' => 'Chrome nail art design',
                'rate' => 2500,
                'barcode' => 'ZA-NAILS-010',
                'status' => 1
            ],
            [
                'name' => 'French Nail Colour Tip',
                'description' => 'French nail color tip design',
                'rate' => 2000,
                'barcode' => 'ZA-NAILS-011',
                'status' => 1
            ],
            [
                'name' => 'Glitter Nails',
                'description' => 'Glitter nail art design',
                'rate' => 2000,
                'barcode' => 'ZA-NAILS-012',
                'status' => 1
            ],
            [
                'name' => 'Nail Art',
                'description' => 'Custom nail art design',
                'rate' => 5000,
                'barcode' => 'ZA-NAILS-013',
                'status' => 1
            ],

            // 9. Hydra Facials
            [
                'name' => 'Aqua Pure Hydrating Facial',
                'description' => 'Removes dead skin cells via suction and infuses skin with hydrating serums',
                'rate' => 12000,
                'barcode' => 'ZA-HYDRA-001',
                'status' => 1
            ],
            [
                'name' => 'Aqua Pure Hydra Oxygenating Facial',
                'description' => 'Gives oxygen level to skin using Vit C and ultra-hydrating solution for red carpet glow',
                'rate' => 14000,
                'barcode' => 'ZA-HYDRA-002',
                'status' => 1
            ],
            [
                'name' => 'ZA Super Hydra Facial',
                'description' => 'Combines cleansing, exfoliation, extraction, hydration, and antioxidants protection',
                'rate' => 16500,
                'barcode' => 'ZA-HYDRA-003',
                'status' => 1
            ],
            [
                'name' => 'Aqua Pure Hydra Lifting Facial',
                'description' => 'Reduces fine lines, increases firmness, evens tone and reduces brown spots',
                'rate' => 20000,
                'barcode' => 'ZA-HYDRA-004',
                'status' => 1
            ],

            // 13. ZA Hammam Experience
            [
                'name' => 'Body Bliss Cleansing (45 mins)',
                'description' => 'Moroccan exfoliating ritual with foam cleansing, polish and mask',
                'rate' => 15000,
                'barcode' => 'ZA-HAMMAM-001',
                'status' => 1
            ],
            [
                'name' => 'Body Reviver Exfoliation (50 mins)',
                'description' => 'Customized scrub and cleansing with traditional Moroccan foam',
                'rate' => 18000,
                'barcode' => 'ZA-HAMMAM-002',
                'status' => 1
            ],
            [
                'name' => 'Royal Pre-Wedding Hammam (60 mins)',
                'description' => 'Complete bridal hammam with steam, Moroccan soap, scrub and bridal pack',
                'rate' => 25000,
                'barcode' => 'ZA-HAMMAM-003',
                'status' => 1
            ],
            [
                'name' => 'Steam & Royal Spa Rituals (90 mins)',
                'description' => 'Ancient Moroccan steam ritual with foam cleansing, scrub, massage and mask',
                'rate' => 28000,
                'barcode' => 'ZA-HAMMAM-004',
                'status' => 1
            ],
            [
                'name' => 'Steam Add-on',
                'description' => 'Additional steam session for any treatment',
                'rate' => 5000,
                'barcode' => 'ZA-HAMMAM-005',
                'status' => 1
            ]
        ];

        // Insert all services
        Service::insert($services);

        $this->command->info('Services seeded successfully! Total services: ' . count($services));
    }
}
