<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryValue;
use App\Models\Coefficient;
use App\Models\CoefficientValue;
use App\Models\EmissionType;
use App\Models\InputField;
use Illuminate\Database\Seeder;

class EmissionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFuel();
        $this->seedExplosives();
        $this->seedLandClearing();
    }

    private function seedFuel(): void
    {
        $type = EmissionType::create([
            'name' => 'Fuel Consumption',
            'slug' => 'fuel',
            'description' => 'Emisi dari pembakaran bahan bakar pada operasional perusahaan.',
            'formula' => 'input_jumlah_fuel * coef_NCV * coef_emission_factor * coef_GWP',
            'formula_display' => 'Jumlah Fuel × NCV × Emission Factor × GWP',
            'unit' => 'tCO2e',
        ]);

        $catFuel = Category::create([
            'emission_type_id' => $type->id,
            'name' => 'jenis_fuel',
            'display_name' => 'Jenis Fuel',
            'sort_order' => 1,
        ]);

        $fuelValues = [
            ['hsd',           'HSD (High-Speed Diesel)'],
            ['pertamax',      'Pertamax'],
            ['avtur',         'Avtur'],
            ['lpg',           'LPG'],
            ['natural_gas',   'Natural Gas'],
            ['mfo',           'MFO (Marine Fuel Oil)'],
            ['biodiesel_b30', 'Biodiesel B30'],
        ];
        $fv = [];
        foreach ($fuelValues as [$code, $label]) {
            $fv[$code] = CategoryValue::create(['category_id' => $catFuel->id, 'code' => $code, 'label' => $label]);
        }

        InputField::create([
            'emission_type_id' => $type->id,
            'name' => 'jumlah_fuel',
            'display_name' => 'Jumlah Fuel',
            'unit' => 'kL',
        ]);

        // NCV (GJ/kL) — IPCC 2006
        $coefNCV = Coefficient::create(['emission_type_id' => $type->id, 'name' => 'NCV', 'display_name' => 'Net Calorific Value (NCV)']);
        $coefNCV->dependentCategories()->attach($catFuel->id);
        foreach ([
            'hsd'           => 35.8,
            'pertamax'      => 34.2,
            'avtur'         => 33.4,
            'lpg'           => 47.3,
            'natural_gas'   => 48.0,
            'mfo'           => 40.4,
            'biodiesel_b30' => 32.6,
        ] as $code => $val) {
            $cv = CoefficientValue::create(['coefficient_id' => $coefNCV->id, 'value' => $val, 'based_on' => 'IPCC 2006']);
            $cv->categoryValues()->attach($fv[$code]->id);
        }

        // Emission Factor (tCO2/GJ) — IPCC 2006
        $coefEF = Coefficient::create(['emission_type_id' => $type->id, 'name' => 'emission_factor', 'display_name' => 'Emission Factor (CO2)']);
        $coefEF->dependentCategories()->attach($catFuel->id);
        foreach ([
            'hsd'           => 0.07420,
            'pertamax'      => 0.06930,
            'avtur'         => 0.07170,
            'lpg'           => 0.06310,
            'natural_gas'   => 0.05620,
            'mfo'           => 0.07700,
            'biodiesel_b30' => 0.07140,
        ] as $code => $val) {
            $cv = CoefficientValue::create(['coefficient_id' => $coefEF->id, 'value' => $val, 'based_on' => 'IPCC 2006']);
            $cv->categoryValues()->attach($fv[$code]->id);
        }

        // GWP (constant) — IPCC AR5
        $coefGWP = Coefficient::create(['emission_type_id' => $type->id, 'name' => 'GWP', 'display_name' => 'Global Warming Potential (GWP)']);
        CoefficientValue::create(['coefficient_id' => $coefGWP->id, 'value' => 1.0, 'based_on' => 'IPCC AR5']);
    }

    private function seedExplosives(): void
    {
        $type = EmissionType::create([
            'name' => 'Explosives',
            'slug' => 'explosives',
            'description' => 'Emisi dari penggunaan bahan peledak pada kegiatan penambangan.',
            'formula' => 'input_jumlah_explosives * coef_emission_factor',
            'formula_display' => 'Jumlah Explosives × Emission Factor',
            'unit' => 'tCO2e',
        ]);

        $catExp = Category::create([
            'emission_type_id' => $type->id,
            'name' => 'jenis_explosives',
            'display_name' => 'Jenis Explosives',
            'sort_order' => 1,
        ]);

        $expValues = [
            ['anfo',       'ANFO'],
            ['emulsion',   'Emulsion'],
            ['heavy_anfo', 'Heavy ANFO'],
            ['tnt',        'TNT'],
            ['dynamite',   'Dynamite'],
            ['det_cord',   'Detonating Cord'],
        ];
        $ev = [];
        foreach ($expValues as [$code, $label]) {
            $ev[$code] = CategoryValue::create(['category_id' => $catExp->id, 'code' => $code, 'label' => $label]);
        }

        InputField::create(['emission_type_id' => $type->id, 'name' => 'jumlah_explosives', 'display_name' => 'Jumlah Explosives', 'unit' => 'kg']);

        // Emission Factor (kgCO2e/kg) — Industry Standard
        $coefEF = Coefficient::create(['emission_type_id' => $type->id, 'name' => 'emission_factor', 'display_name' => 'Emission Factor']);
        $coefEF->dependentCategories()->attach($catExp->id);
        foreach ([
            'anfo'       => 0.2670,
            'emulsion'   => 0.1900,
            'heavy_anfo' => 0.2300,
            'tnt'        => 0.4870,
            'dynamite'   => 0.5120,
            'det_cord'   => 0.1650,
        ] as $code => $val) {
            $cv = CoefficientValue::create(['coefficient_id' => $coefEF->id, 'value' => $val, 'based_on' => 'Industry Standard']);
            $cv->categoryValues()->attach($ev[$code]->id);
        }
    }

    private function seedLandClearing(): void
    {
        $type = EmissionType::create([
            'name' => 'Land Clearing',
            'slug' => 'land_clearing',
            'description' => 'Emisi dari pembersihan lahan untuk kegiatan operasional.',
            'formula' => 'input_luas_lahan * coef_konstanta_hutan * coef_rata_rata_biomass * coef_emission_factor',
            'formula_display' => 'Luas Lahan × Konstanta Hutan × Rata-rata Biomass × Emission Factor',
            'unit' => 'tCO2e',
        ]);

        $catHutan = Category::create(['emission_type_id' => $type->id, 'name' => 'jenis_hutan',      'display_name' => 'Jenis Hutan',      'sort_order' => 1]);
        $catGeo   = Category::create(['emission_type_id' => $type->id, 'name' => 'tipe_geografi',    'display_name' => 'Tipe Geografi',    'sort_order' => 2]);
        $catPlant = Category::create(['emission_type_id' => $type->id, 'name' => 'tipe_plantation',  'display_name' => 'Tipe Plantation',  'sort_order' => 3]);

        $hv = [];
        foreach ([['hutan_primer','Hutan Primer'],['hutan_sekunder','Hutan Sekunder'],['hutan_mangrove','Hutan Mangrove'],['lahan_gambut','Lahan Gambut'],['semak_belukar','Semak Belukar'],['padang_rumput','Padang Rumput']] as [$code,$label]) {
            $hv[$code] = CategoryValue::create(['category_id' => $catHutan->id, 'code' => $code, 'label' => $label]);
        }
        $gv = [];
        foreach ([['dataran_rendah','Dataran Rendah'],['dataran_tinggi','Dataran Tinggi'],['pesisir','Pesisir'],['rawa','Rawa']] as [$code,$label]) {
            $gv[$code] = CategoryValue::create(['category_id' => $catGeo->id, 'code' => $code, 'label' => $label]);
        }
        $pv = [];
        foreach ([['akasia','Akasia'],['eucalyptus','Eucalyptus'],['kelapa_sawit','Kelapa Sawit'],['jati','Jati'],['pinus','Pinus'],['karet','Karet'],['mixed_species','Mixed Species']] as [$code,$label]) {
            $pv[$code] = CategoryValue::create(['category_id' => $catPlant->id, 'code' => $code, 'label' => $label]);
        }

        InputField::create(['emission_type_id' => $type->id, 'name' => 'luas_lahan', 'display_name' => 'Luas Lahan', 'unit' => 'ha']);

        // Konstanta Hutan — depends on [jenis_hutan, tipe_geografi] — IPCC 2006 GL
        $coefKH = Coefficient::create(['emission_type_id' => $type->id, 'name' => 'konstanta_hutan', 'display_name' => 'Konstanta Hutan']);
        $coefKH->dependentCategories()->attach([$catHutan->id, $catGeo->id]);
        foreach ([
            ['hutan_primer',   'dataran_rendah', 0.470], ['hutan_primer',   'dataran_tinggi', 0.380],
            ['hutan_primer',   'pesisir',        0.420], ['hutan_primer',   'rawa',           0.350],
            ['hutan_sekunder', 'dataran_rendah', 0.310], ['hutan_sekunder', 'dataran_tinggi', 0.260],
            ['hutan_sekunder', 'pesisir',        0.290], ['hutan_sekunder', 'rawa',           0.240],
            ['hutan_mangrove', 'dataran_rendah', 0.390], ['hutan_mangrove', 'dataran_tinggi', 0.320],
            ['hutan_mangrove', 'pesisir',        0.450], ['hutan_mangrove', 'rawa',           0.410],
            ['lahan_gambut',   'dataran_rendah', 0.520], ['lahan_gambut',   'dataran_tinggi', 0.480],
            ['lahan_gambut',   'pesisir',        0.490], ['lahan_gambut',   'rawa',           0.560],
            ['semak_belukar',  'dataran_rendah', 0.180], ['semak_belukar',  'dataran_tinggi', 0.150],
            ['semak_belukar',  'pesisir',        0.170], ['semak_belukar',  'rawa',           0.140],
            ['padang_rumput',  'dataran_rendah', 0.090], ['padang_rumput',  'dataran_tinggi', 0.075],
            ['padang_rumput',  'pesisir',        0.085], ['padang_rumput',  'rawa',           0.070],
        ] as [$h, $g, $val]) {
            $cv = CoefficientValue::create(['coefficient_id' => $coefKH->id, 'value' => $val, 'based_on' => 'IPCC 2006 GL']);
            $cv->categoryValues()->attach([$hv[$h]->id, $gv[$g]->id]);
        }

        // Rata-rata Biomass — depends on [tipe_geografi, tipe_plantation] — IPCC 2006 GL
        $coefBM = Coefficient::create(['emission_type_id' => $type->id, 'name' => 'rata_rata_biomass', 'display_name' => 'Rata-rata Biomass']);
        $coefBM->dependentCategories()->attach([$catGeo->id, $catPlant->id]);
        foreach ([
            ['dataran_rendah','akasia',120.0],['dataran_rendah','eucalyptus',140.0],['dataran_rendah','kelapa_sawit',80.0],['dataran_rendah','jati',160.0],
            ['dataran_rendah','pinus',130.0],['dataran_rendah','karet',110.0],['dataran_rendah','mixed_species',135.0],
            ['dataran_tinggi','akasia',100.0],['dataran_tinggi','eucalyptus',120.0],['dataran_tinggi','kelapa_sawit',65.0],['dataran_tinggi','jati',140.0],
            ['dataran_tinggi','pinus',150.0],['dataran_tinggi','karet',90.0],['dataran_tinggi','mixed_species',115.0],
            ['pesisir','akasia',95.0],['pesisir','eucalyptus',110.0],['pesisir','kelapa_sawit',75.0],['pesisir','jati',130.0],
            ['pesisir','pinus',105.0],['pesisir','karet',85.0],['pesisir','mixed_species',100.0],
            ['rawa','akasia',85.0],['rawa','eucalyptus',95.0],['rawa','kelapa_sawit',60.0],['rawa','jati',110.0],
            ['rawa','pinus',90.0],['rawa','karet',75.0],['rawa','mixed_species',88.0],
        ] as [$g, $p, $val]) {
            $cv = CoefficientValue::create(['coefficient_id' => $coefBM->id, 'value' => $val, 'based_on' => 'IPCC 2006 GL']);
            $cv->categoryValues()->attach([$gv[$g]->id, $pv[$p]->id]);
        }

        // Emission Factor — depends on [tipe_plantation] — National Inventory
        $coefEF = Coefficient::create(['emission_type_id' => $type->id, 'name' => 'emission_factor', 'display_name' => 'Emission Factor']);
        $coefEF->dependentCategories()->attach($catPlant->id);
        foreach ([
            'akasia'       => 0.4700, 'eucalyptus'  => 0.4900, 'kelapa_sawit' => 0.5500,
            'jati'         => 0.4300, 'pinus'        => 0.4600, 'karet'        => 0.5100,
            'mixed_species'=> 0.4800,
        ] as $code => $val) {
            $cv = CoefficientValue::create(['coefficient_id' => $coefEF->id, 'value' => $val, 'based_on' => 'National Inventory']);
            $cv->categoryValues()->attach($pv[$code]->id);
        }
    }
}
