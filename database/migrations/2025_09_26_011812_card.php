<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create users table if it doesn't exist
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id('user_id');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['admin', 'leader', 'user'])->default('user');
                $table->enum('current_task_status', ['available', 'working', 'break'])->default('available');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Create projects table
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id('project_id');
                $table->string('project_name');
                $table->text('description')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['planning', 'in_progress', 'completed', 'on_hold'])->default('planning');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('created_by')->references('user_id')->on('users');
            });
        }

        // Create boards table
        if (!Schema::hasTable('boards')) {
            Schema::create('boards', function (Blueprint $table) {
                $table->id('board_id');
                $table->unsignedBigInteger('project_id');
                $table->string('board_name');
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->foreign('project_id')->references('project_id')->on('projects');
            });
        }

        // Create cards table
        if (!Schema::hasTable('cards')) {
            Schema::create('cards', function (Blueprint $table) {
                $table->id('card_id');
                $table->unsignedBigInteger('board_id');
                $table->string('card_title');
                $table->text('description')->nullable();
                $table->integer('position')->default(0);
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->date('due_date')->nullable();
                $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'blocked'])->default('todo');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->decimal('estimated_hours', 8, 2)->nullable();
                $table->decimal('actual_hours', 8, 2)->nullable();
                $table->timestamps();
                
                $table->foreign('board_id')->references('board_id')->on('boards');
                $table->foreign('created_by')->references('user_id')->on('users');
                $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('set null');
            });
        }

        // Create related tables for the comprehensive project management system

        // Time logs table for time tracking
        if (!Schema::hasTable('time_logs')) {
            Schema::create('time_logs', function (Blueprint $table) {
                $table->id('time_log_id');
                $table->unsignedBigInteger('card_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('start_time');
                $table->timestamp('end_time')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->foreign('card_id')->references('card_id')->on('cards');
                $table->foreign('user_id')->references('user_id')->on('users');
            });
        }

        // Card assignments table
        if (!Schema::hasTable('card_assignments')) {
            Schema::create('card_assignments', function (Blueprint $table) {
                $table->id('assignment_id');
                $table->unsignedBigInteger('card_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('assignment_status', ['assigned', 'accepted', 'rejected', 'completed']);
                $table->timestamp('assigned_at')->useCurrent();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->foreign('card_id')->references('card_id')->on('cards');
                $table->foreign('user_id')->references('user_id')->on('users');
            });
        }

        // Comments table for task communication
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id('comment_id');
                $table->unsignedBigInteger('card_id');
                $table->unsignedBigInteger('user_id');
                $table->text('content');
                $table->timestamps();
                
                $table->foreign('card_id')->references('card_id')->on('cards');
                $table->foreign('user_id')->references('user_id')->on('users');
            });
        }

        // Help requests table for developer/designer to team lead communication
        if (!Schema::hasTable('help_requests')) {
            Schema::create('help_requests', function (Blueprint $table) {
                $table->id('help_request_id');
                $table->unsignedBigInteger('card_id');
                $table->unsignedBigInteger('requester_id');
                $table->unsignedBigInteger('team_lead_id')->nullable();
                $table->string('subject');
                $table->text('message');
                $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->timestamps();
                
                $table->foreign('card_id')->references('card_id')->on('cards');
                $table->foreign('requester_id')->references('user_id')->on('users');
                $table->foreign('team_lead_id')->references('user_id')->on('users')->onDelete('set null');
            });
        }

        // Notifications table
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id('notification_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('from_user_id')->nullable();
                $table->string('title');
                $table->text('message');
                $table->enum('type', ['task_assigned', 'help_request', 'comment', 'status_update', 'system']);
                $table->unsignedBigInteger('related_id')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                
                $table->foreign('user_id')->references('user_id')->on('users');
                $table->foreign('from_user_id')->references('user_id')->on('users')->onDelete('set null');
            });
        }

        // Subtasks table
        if (!Schema::hasTable('subtasks')) {
            Schema::create('subtasks', function (Blueprint $table) {
                $table->id('subtask_id');
                $table->unsignedBigInteger('card_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
                $table->integer('position')->default(0);
                $table->timestamps();
                
                $table->foreign('card_id')->references('card_id')->on('cards');
            });
        }

        // Project members table
        if (!Schema::hasTable('project_members')) {
            Schema::create('project_members', function (Blueprint $table) {
                $table->id('member_id');
                $table->unsignedBigInteger('project_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('role', ['admin', 'leader', 'member'])->default('member');
                $table->timestamps();
                
                $table->foreign('project_id')->references('project_id')->on('projects');
                $table->foreign('user_id')->references('user_id')->on('users');
                $table->unique(['project_id', 'user_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('subtasks');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('help_requests');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('card_assignments');
        Schema::dropIfExists('time_logs');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('boards');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');
    }
};
