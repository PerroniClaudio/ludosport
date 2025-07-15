<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromArray;

class TemplateExport implements FromArray {

    private $type;
    private $eventId;

    public function __construct($type, $eventId = null) {
        $this->type = $type;
        $this->eventId = $eventId;
    }

    public function array(): array {
        $template_data = [];
        $headers = $this->getHeadersForType($this->type);

        
        // Quando si modificano i campi del template (getHeadersForType) vanno modificati anche i dati inseriti nel template (template_data)
        if(in_array($this->type, ['event_war', 'event_style', 'event_instructor_results'])
            && $this->eventId != null
        ){
            $event = Event::find($this->eventId);
            if($event){
                if($this->type == 'event_instructor_results'){
                    // Evento istruttore
                    $template_data = $event->instructorResults->map(function ($result) use ($event) {
                        return [
                            $this->eventId,
                            $result->user->email,
                            $result->weapon_form_id ? $result->weapon_form_id : $event->weapon_form_id,
                            '',
                            '',
                            '',
                            '',
                            '',
                            $result->user->name ?? '',
                            $result->user->surname ?? ''
                        ];
                    })->toArray();
                } else {
                    // Evento ranking
                    $template_data = $event->results->map(function ($result) {
                        return [
                            $this->eventId,
                            $result->user->email,
                            '',
                            $result->user->battle_name,
                            $result->user->name . ' ' . $result->user->surname ?? ''

                        ];
                    })->toArray();
                }
            }            
        }

        return [
            $headers,
            $template_data
        ];
    }

    // Quando si modificano i campi del template (getHeadersForType) vanno modificati anche i dati inseriti nel template (template_data)
    private function getHeadersForType($type) {
        switch ($type) {
            case 'new_users':

                return [
                    "Name *",
                    "Surname *",
                    "Email *",
                    "Nationality (Name of the country) *",
                    "Academy ID"
                ];

                break;
            case 'users_course':

                return [
                    "Email *",
                    "Course ID *"
                ];

                break;
            case 'users_academy':

                return [
                    "Email *",
                    "Academy ID *"
                ];

                break;
            case 'users_school':

                return [
                    "Email *",
                    "School ID *"
                ];

                break;

            case 'event_participants':

                return [
                    "Event ID *",
                    "User Email *",
                ];

                break;
            case 'event_war':

                return [
                    "Event ID *",
                    "User Email *",
                    "Position *",
                    "Battle name",
                    "Full name",
                ];

                break;
            case 'event_style':

                return [
                    "Event ID *",
                    "User Email *",
                    "Position *",
                    "Battle name",
                    "Full name",
                ];

                break;
            case 'event_instructor_participants':

                return [
                    "Event ID *",
                    "User Email *",
                ];

                break;
            case 'event_instructor_results':
                
                // return [
                //     "Event ID *",
                //     "User Email *",
                //     "Weapon Form ID (If missing, is set to event's weapon form)",
                //     "Result (passed/review/failed) *",
                //     "Notes (max 100 chars)",
                //     "Battle name",
                //     "Full name",
                // ];

                return [
                    "Event ID *",
                    "User Email *",
                    "Weapon Form ID (If missing, is set to event's Weapon Form)",
                    "Result (Green/Yellow/Red) *",
                    "Internship Duration (* If result is Yellow)",
                    "Notes on the Internship (* If result is Yellow, max 100 chars)",
                    "Retake Exam or retake Course? (write only 'Exam' or 'Course') (* If result is Red)",
                    "Notes (max 100 chars)",
                    "Name",
                    "Surname",
                ];

                break;
            default:
                break;
        }
    }
}
