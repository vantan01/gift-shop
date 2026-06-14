<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Tất cả test trong thư mục Feature đều dùng RefreshDatabase
uses(TestCase::class, RefreshDatabase::class)->in('Feature');