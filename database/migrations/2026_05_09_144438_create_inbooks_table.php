<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //this one
        //incoming stock
        //inbooks -- inbook_items -- inventory -- section -- warehouse
        Schema::create('inbooks', function (Blueprint $table) {
            $table->id();
            
            //$table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            //supplier_id
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('storage_date');
            //warehouse_id
            //supplier_id
            //uploaded_by
            //date
            //status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbooks');
    }
};
