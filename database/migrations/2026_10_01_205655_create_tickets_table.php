<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description');
            $table->string('priority')->default('low');
            $table->string('status')->default('open');
            $table->timestamps();
        });

        // SQLite does not enforce VARCHAR lengths, so guard inserts and updates.
        if (DB::getDriverName() === 'sqlite') {
            foreach (['INSERT', 'UPDATE OF title'] as $event) {
                $name = $event === 'INSERT' ? 'tickets_title_insert' : 'tickets_title_update';

                DB::unprepared("CREATE TRIGGER {$name}
                    BEFORE {$event} ON tickets
                    FOR EACH ROW WHEN length(NEW.title) > 150
                    BEGIN
                        SELECT RAISE(ABORT, 'Ticket title must not exceed 150 characters');
                    END");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
