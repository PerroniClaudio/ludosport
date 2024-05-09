{{-- 
    
    È stato necessario effettuare un componente separato poiché Nazionalitò, Accademia e Scuola sono strettamente correlate tra loro.

--}}

@props(['nationality' => '', 'selectedAcademyId' => '', 'selectedSchoolId' => ''])

<div 
    x-data="{ 
        selectedNationality: '{{ $nationality }}',
        selectedAcademyId: '{{ $selectedAcademyId }}',
        selectedSchoolId: '{{ $selectedSchoolId }}',
        academies: [],
        schools: [],
        
    }"
>




</div>