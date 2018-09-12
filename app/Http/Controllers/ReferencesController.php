<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;
use Barryvdh\Debugbar\Facade as Debugbar;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Applicant;
use App\Models\Reference;
use App\Models\Lookup\Relationship;

class ReferencesController extends Controller
{

    /**
     * Display the Skills page associated with the applicant.
     *
     * @param  \App\Models\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function show(Applicant $applicant)
    {
        //

    }

    /**
     * Show the form for editing the applicant's references
     *
     * @param  Request  $request
     * @param  \App\Models\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Applicant $applicant)
    {
        return view('applicant/profile_04_references', [
            'applicant' => $applicant,
            'profile' => Lang::get('applicant/profile_references'),
            'relative_template' => Lang::get('common/relatives'),
            'skills' => Skill::all(),
            'relationships' => Relationship::all(),
            'form_submit_action' => route('profile.references.update', $applicant),
        ]);
    }

    /**
     * Update the applicant's references in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Applicant $applicant)
    {

        $input = $request->input();
        $shiftedInput = $this->shiftFirstLevelArrayKeysToBottom($input);

        Debugbar::info($input);
        Debugbar::info($shiftedInput);

        //Save new skill declarations
        if (isset($shiftedInput['new']) && is_array($shiftedInput['new'])) {
            $refInputs = $shiftedInput['new'];
            Debugbar::info($refInputs);
            foreach($refInputs as $refInput) {
                Debugbar::info($refInput);
                $reference = new Reference();
                $reference->applicant_id = $applicant->id;
                //All new skills start with 'claimed' status
                $reference->name = $refInput['reference_name'];
                $reference->email = $refInput['reference_email'];
                $reference->relationship_id = $refInput['reference_relationship'];
                $reference->description = $refInput['reference_description'];

                $reference->save();
            }
        }

        //Update old skill declarations
        // if (isset($shiftedInput['old']) && is_array($shiftedInput['old'])) {
        //     //Save new skill declarations
        //     $refInputs = $shiftedInput['old'];
        //     foreach($refInputs as $id=>$refInput) {
        //         $reference = $applicant->skill_declarations->where('id', $id)->first();
        //         //Ensure input can be connected to an existing declaration
        //         if ($reference != null) {
        //             $reference->description = $refInput['skill_description'];
        //             $reference->skill_level_id = $refInput['skill_level'];
        //
        //             $reference->save();
        //         } else {
        //             Debugbar::warning('Applicant '.$applicant->id.' attempted to update Skill Declaration with invalid id '.$criteria_id);
        //         }
        //     }
        // }



        //return redirect( route('profile.skills.edit', $applicant) );
        return view('applicant/profile_04_references', [
            'applicant' => $applicant,
            'profile' => Lang::get('applicant/profile_references'),
            'relative_template' => Lang::get('common/relatives'),
            'skills' => Skill::all(),
            'relationships' => Relationship::all(),
            'form_submit_action' => route('profile.references.update', $applicant),
        ]);
    }

}
