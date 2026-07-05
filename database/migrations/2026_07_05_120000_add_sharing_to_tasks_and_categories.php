<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'share_token')) {
                $table->string('share_token')->nullable()->unique()->after('user_id');
            }
            if (!Schema::hasColumn('tasks', 'share_can_edit')) {
                $table->boolean('share_can_edit')->default(false)->after('share_token');
            }
            if (!Schema::hasColumn('tasks', 'share_can_complete')) {
                $table->boolean('share_can_complete')->default(false)->after('share_can_edit');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'share_token')) {
                $table->string('share_token')->nullable()->unique()->after('profile_image');
            }
            if (!Schema::hasColumn('users', 'share_can_edit')) {
                $table->boolean('share_can_edit')->default(false)->after('share_token');
            }
            if (!Schema::hasColumn('users', 'share_can_complete')) {
                $table->boolean('share_can_complete')->default(false)->after('share_can_edit');
            }
        });

        if (!Schema::hasTable('category_shares')) {
            Schema::create('category_shares', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
                $table->string('share_token')->unique();
                $table->boolean('can_edit')->default(false);
                $table->boolean('can_complete')->default(false);
                $table->timestamps();
            });
        } else {
            Schema::table('category_shares', function (Blueprint $table) {
                if (!Schema::hasColumn('category_shares', 'can_edit')) {
                    $table->boolean('can_edit')->default(false);
                }
                if (!Schema::hasColumn('category_shares', 'can_complete')) {
                    $table->boolean('can_complete')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_shares');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'share_token')) {
                $table->dropColumn('share_token');
            }
            if (Schema::hasColumn('users', 'share_can_edit')) {
                $table->dropColumn('share_can_edit');
            }
            if (Schema::hasColumn('users', 'share_can_complete')) {
                $table->dropColumn('share_can_complete');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'share_token')) {
                $table->dropColumn('share_token');
            }
            if (Schema::hasColumn('tasks', 'share_can_edit')) {
                $table->dropColumn('share_can_edit');
            }
            if (Schema::hasColumn('tasks', 'share_can_complete')) {
                $table->dropColumn('share_can_complete');
            }
        });
    }
};
