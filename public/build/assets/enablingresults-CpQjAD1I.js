const a=r=>({submitEnablingResult:async function(n,s,l=null){const o=`/submit-enabling-result/${r}`,t=new FormData;t.append("result_id",n),t.append("result",s),t.append("retake",l);const i=await fetch(o,{method:"POST",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:t});if(i.ok){const e=await i.json();e.success&&(this.row.result=e.result.result,this.row.stage=e.result.stage,this.row.retake=e.result.retake)}},notesModal:{resultid:null,notes:null,internshipDuration:null,internshipNotes:null,retake:null},openNotesModal:function(n,s=null,l=null,o=null,t=null){this.notesModal.resultid=n,this.notesModal.notes=s,this.notesModal.internshipDuration=l,this.notesModal.internshipNotes=o,this.notesModal.retake=t},init:async function(){}});export{a as enablingresults};