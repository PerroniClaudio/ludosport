<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class TemplateExport implements FromArray {

    private $type;

    public function __construct($type) {
        $this->type = $type;
    }

    public function array(): array {
        $template_data = [];
        $headers = $this->getHeadersForType($this->type);

        return [
            $headers,
            $template_data
        ];
    }

    private function getHeadersForType($type) {
        switch ($type) {
            case 'new_users':

                return [
                    "Name *",
                    "Surname *",
                    "Email *",
                    "Nationality",
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
            default:
                break;
        }
    }
}
