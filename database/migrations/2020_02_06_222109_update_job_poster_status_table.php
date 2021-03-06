<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateJobPosterStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $statuses = [
            'draft' => json_encode([
                'en' => 'Draft',
                'fr' => 'Provisoire'
            ]),
            'review_manager' => json_encode([
                'en' => 'In Review (Manager)',
                'fr' => 'En revue (Gestion)',
            ]),
            'review_hr' => json_encode([
                'en' => 'In Review (HR)',
                'fr' => 'En revue (RH)',
            ]),
            'translation' => json_encode([
                'en' => 'In Translation',
                'fr' => 'En traduction',
            ]),
            'final_review_manager' => json_encode([
                'en' => 'Final Review (Manager)',
                'fr' => 'Révision finale (Gestion)'
            ]),
            'final_review_hr' => json_encode([
                'en' => 'Final Review (HR)',
                'fr' => 'Révision finale (RH)',
            ]),
            'pending_approval' => json_encode([
                'en' => 'Pending Approval',
                'fr' => 'En attente d\'approbation'
            ]),
            'approved' => json_encode([
                'en' => 'Approved',
                'fr' => 'Approuvé'
            ]),
            'ready' => json_encode([
                'en' => 'Ready to Post',
                'fr' => 'Prêt à poster'
            ]),
            'live' => json_encode([
                'en' => 'Live',
                'fr' => 'En ligne',
            ]),
            'assessment' => json_encode([
                'en' => 'In Assessment',
                'fr' => 'En cours d\'évaluation',
            ]),
            'completed' => json_encode([
                'en' => 'Completed',
                'fr' => 'Terminé'
            ])
        ];

        DB::table('job_poster_status')->truncate();
        foreach ($statuses as $name => $value) {
            DB::table('job_poster_status')->insert([
                'name' => $name, 'value' => $value
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $oldStatuses = [
            'draft' => json_encode([
                'en' => 'Draft',
                'fr' => 'Provisoire'
            ]),
            'submitted' => json_encode([
                'en' => 'Submitted',
                'fr' => 'Soumis'
            ]),
            'pending' => json_encode([
                'en' => 'Pending',
                'fr' => 'Pendant'
            ]),
            'approved' => json_encode([
                'en' => 'Approved',
                'fr' => 'Approuvé'
            ]),
            'published' => json_encode([
                'en' => 'Published',
                'fr' => 'Publié'
            ]),
            'closed' => json_encode([
                'en' => 'Closed',
                'fr' => 'Fermée'
            ]),
            'complete' => json_encode([
                'en' => 'Completed',
                'fr' => 'Terminé'
            ])
        ];
        DB::table('job_poster_status')->truncate();
        foreach ($oldStatuses as $name => $value) {
            DB::table('job_poster_status')->insert([
                'name' => $name, 'value' => $value
            ]);
        }
    }
}
