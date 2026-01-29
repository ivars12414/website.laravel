<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_method_id');
            $table->unsignedInteger('lang_id');
            $table->string('name')->nullable();
            $table->string('descr', 2000)->nullable();
            $table->string('btn_txt', 255)->nullable();
            $table->unique(['payment_method_id', 'lang_id']);
        });

        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'payment_method_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedInteger('payment_method_id')->nullable()->after('payment_method_hash');
            });
        }

        if (!Schema::hasTable('payment_methods')) {
            return;
        }

        $methods = DB::table('payment_methods')->orderBy('id')->get();
        if ($methods->isEmpty()) {
            $this->dropPaymentMethodColumns();
            $this->dropPaymentHashColumn();
            return;
        }

        $hashToId = [];
        foreach ($methods as $method) {
            $groupKey = $method->hash ?: (string)$method->id;
            if (!isset($hashToId[$groupKey])) {
                $hashToId[$groupKey] = $method->id;
            }

            DB::table('payment_method_translations')->updateOrInsert(
                [
                    'payment_method_id' => $hashToId[$groupKey],
                    'lang_id' => $method->lang_id,
                ],
                [
                    'name' => $method->name,
                    'descr' => $method->descr,
                    'btn_txt' => $method->btn_txt,
                ]
            );
        }

        $keepIds = array_values($hashToId);
        if ($keepIds) {
            DB::table('payment_methods')->whereNotIn('id', $keepIds)->delete();
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'payment_method_hash')) {
            foreach ($hashToId as $hash => $id) {
                if ($hash === null || $hash === '') {
                    continue;
                }

                DB::table('payments')
                    ->where('payment_method_hash', $hash)
                    ->update(['payment_method_id' => $id]);
            }
        }

        $this->dropPaymentMethodColumns();
        $this->dropPaymentHashColumn();
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_methods')) {
            $columns = Schema::getColumnListing('payment_methods');
            Schema::table('payment_methods', function (Blueprint $table) use ($columns) {
                if (!in_array('hash', $columns, true)) {
                    $table->string('hash', 32)->nullable();
                }
                if (!in_array('lang_id', $columns, true)) {
                    $table->unsignedInteger('lang_id')->default(0);
                }
                if (!in_array('name', $columns, true)) {
                    $table->string('name')->nullable();
                }
                if (!in_array('descr', $columns, true)) {
                    $table->string('descr', 2000)->nullable();
                }
                if (!in_array('btn_txt', $columns, true)) {
                    $table->string('btn_txt', 255)->nullable();
                }
            });

            if (Schema::hasTable('payment_method_translations')) {
                $translations = DB::table('payment_method_translations')->get();
                foreach ($translations as $translation) {
                    DB::table('payment_methods')
                        ->where('id', $translation->payment_method_id)
                        ->update([
                            'lang_id' => $translation->lang_id,
                            'name' => $translation->name,
                            'descr' => $translation->descr,
                            'btn_txt' => $translation->btn_txt,
                        ]);
                }
            }
        }

        if (Schema::hasTable('payments')) {
            if (!Schema::hasColumn('payments', 'payment_method_hash')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('payment_method_hash', 32)->nullable();
                });
            }

            if (Schema::hasColumn('payments', 'payment_method_id')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropColumn('payment_method_id');
                });
            }
        }

        Schema::dropIfExists('payment_method_translations');
    }

    private function dropPaymentMethodColumns(): void
    {
        $columns = Schema::getColumnListing('payment_methods');
        $drop = array_values(array_intersect(['hash', 'lang_id', 'name', 'descr', 'btn_txt'], $columns));

        if (!$drop) {
            return;
        }

        Schema::table('payment_methods', function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }

    private function dropPaymentHashColumn(): void
    {
        if (!Schema::hasTable('payments') || !Schema::hasColumn('payments', 'payment_method_hash')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method_hash');
        });
    }
};
