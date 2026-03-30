<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateDefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            'company_name',
            'company_address',
            'company_email',
            'company_contact_no',
            'company_terms_conditions',
            'company_gst_settings',
            'site_logo',
            'site_favicon',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
        ];
        $created_at = now();
        $updated_at = now();

        foreach ($attributes as $attribute) {
            \DB::table('settings')->insert(compact('attribute', 'created_at', 'updated_at'));
        }
    }
}
