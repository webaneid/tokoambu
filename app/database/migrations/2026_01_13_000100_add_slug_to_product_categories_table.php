<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        $categories = \App\Models\ProductCategory::query()->get();
        foreach ($categories as $category) {
            $baseSlug = Str::slug($category->name);
            $slug = $baseSlug !== '' ? $baseSlug : 'kategori';

            $suffix = 1;
            while (\App\Models\ProductCategory::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug !== '' ? "{$baseSlug}-{$suffix}" : "kategori-{$suffix}";
                $suffix++;
            }

            $category->slug = $slug;
            $category->save();
        }
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
