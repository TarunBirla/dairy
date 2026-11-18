<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class DeductionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_hi',
        'code',
        'description',
        'status',
        'created_by',
    ];

    /**
     * Resiliently ensure deduction_categories table exists and default data is seeded
     */
    public static function ensureTableExistsAndSeeded(): void
    {
        try {
            if (!Schema::hasTable('deduction_categories')) {
                Schema::create('deduction_categories', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('name_hi')->nullable();
                    $table->string('code')->unique();
                    $table->text('description')->nullable();
                    $table->string('status', 20)->default('active'); // active, inactive
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->timestamps();
                });
            }

            // Seed default categories if table is empty
            if (self::count() === 0) {
                $defaults = [
                    ['name' => 'Cattle Feed', 'name_hi' => 'पशु आहार', 'code' => 'cattle_feed', 'description' => 'Khalli, chokar, cattle feed rations'],
                    ['name' => 'Medicine', 'name_hi' => 'दवाई', 'code' => 'medicine', 'description' => 'Veterinary medicines and supplements'],
                    ['name' => 'Ghee / Butter', 'name_hi' => 'घी / मक्खन', 'code' => 'ghee_butter', 'description' => 'Dairy products taken on credit'],
                    ['name' => 'Doctor Fee', 'name_hi' => 'डॉक्टर शुल्क', 'code' => 'doctor_fee', 'description' => 'Veterinary visit and treatment fees'],
                    ['name' => 'Insurance', 'name_hi' => 'बीमा', 'code' => 'insurance', 'description' => 'Cattle and livestock insurance deduction'],
                    ['name' => 'Advance Recovery', 'name_hi' => 'अग्रिम वसूली', 'code' => 'advance_recovery', 'description' => 'Installment and advance recovery deduction'],
                    ['name' => 'Store Purchase', 'name_hi' => 'स्टोर खरीद', 'code' => 'store_purchase', 'description' => 'Purchases from dairy cooperative store'],
                    ['name' => 'Equipment', 'name_hi' => 'उपकरण / बर्तन', 'code' => 'equipment', 'description' => 'Milk cans, strainers, and dairy equipments'],
                ];

                foreach ($defaults as $item) {
                    self::create([
                        'name' => $item['name'],
                        'name_hi' => $item['name_hi'],
                        'code' => $item['code'],
                        'description' => $item['description'],
                        'status' => 'active',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore in background
        }
    }

    /**
     * Get active categories list formatted for dropdowns
     */
    public static function getActiveList(): array
    {
        self::ensureTableExistsAndSeeded();
        try {
            $categories = self::where('status', 'active')->orderBy('name')->get();
            $list = [];
            foreach ($categories as $cat) {
                $label = $cat->name;
                if (!empty($cat->name_hi)) {
                    $label .= " ({$cat->name_hi})";
                }
                $list[$cat->code] = $label;
            }
            return $list;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deductions()
    {
        return $this->hasMany(FarmerDeduction::class, 'deduction_type', 'code');
    }
}
