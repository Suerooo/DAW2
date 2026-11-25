<?php

namespace Database\Seeders;

use App\Models\Comment as ModelsComment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comment = new ModelsComment();
        $comment->user_id = 1;
        $comment->text = "Test";
        $comment->save();
    }
}
