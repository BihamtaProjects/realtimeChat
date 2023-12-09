<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Console;

use DB;
use Illuminate\Console\Command;
use Throwable;

class MigrateYii2AutoPrivateQuestionWord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ConsultingChat:migrate-yii2-auto-private-question-word';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yii2Data = DB::table('tbl_auto_private_question_word')->get();

        foreach ($yii2Data as $yii2) {
            try {
                DB::table('private_words')->insert([
                    'id' => $yii2->id,
                    'word' => $yii2->word,
                    ]);
            } catch(Throwable $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

}


