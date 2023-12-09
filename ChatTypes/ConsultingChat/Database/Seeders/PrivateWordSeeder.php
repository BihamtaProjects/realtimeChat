<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PrivateWordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $oldDbPrivateWords=  DB::table('tbl_auto_private_question_word')->get()->all();
        $privateWord = [];
        foreach ($oldDbPrivateWords as $oldDbPrivateWord) {
            $privateWord[] = [
                'id' => $oldDbPrivateWord->id,
                'word' => $oldDbPrivateWord->word,];
        }
        DB::table('private_words')->insert($privateWord);
    }



}
