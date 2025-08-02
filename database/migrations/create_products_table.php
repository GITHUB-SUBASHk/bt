<?php
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->date('dob');
        $table->string('languages');
        $table->string('country');
        $table->string('state');
        $table->string('city');
        $table->boolean('email_verified')->default(false);
        $table->string('email_token')->nullable();
        $table->string('otp')->nullable();
        $table->string('password')->nullable(); // Will be null until verified
        $table->rememberToken();
        $table->timestamps();
    });
}
?>
