import{C as i}from"./auto-CdBL8C_c.js";const d=(h,o,c)=>({academy:null,schoolData:[],filteredSchoolData:[],academyYearData:[],currentSchoolsPage:1,totalSchoolsPages:1,paginatedSchools:[],colors:["rgb(237,116,0)","rgb(212, 145, 255)","rgb(179,4,16)","rgb(0,94,152)","rgb(0,129,57)"],async getAcademyYearData(){return await(await fetch(`${h=="admin"?"":"/"+h}/academies/${o.id}/athletes-year-data`)).json()},createGraph(){const t=document.getElementById("userschoolgraph").getContext("2d"),a=this.schoolData.map(s=>s.name),e=this.schoolData.map(s=>s.athletes),l={type:"pie",data:{labels:a,datasets:[{label:"Athletes",data:e,backgroundColor:this.colors,hoverOffset:4}]}};new i(t,l)},searchSchoolByValue(t){const a=t.target.value.toLowerCase();a!=""?(this.filteredSchoolData=this.schoolData.filter(e=>e.name.toLowerCase().includes(a)),this.updateSchools()):(this.filteredSchoolData=this.schoolData,this.updateSchools())},nextPage(){this.currentSchoolsPage<this.totalSchoolsPages&&(this.currentSchoolsPage++,this.updateSchools())},previousPage(){this.currentSchoolsPage>1&&(this.currentSchoolsPage--,this.updateSchools())},updateSchools(){const t=(this.currentSchoolsPage-1)*10;this.totalSchoolsPages=Math.ceil(this.filteredSchoolData.length/10),this.paginatedSchools=this.filteredSchoolData.slice(t,t+10)},async init(){console.log("userschoolgraph initialized"),this.academy=o,console.log(o.name),this.schoolData=c,this.academyYearData=await this.getAcademyYearData(),this.createGraph(),this.filteredSchoolData=this.schoolData,this.totalSchoolsPages=Math.ceil(this.filteredSchoolData.length/10),this.updateSchools(),this.$dispatch("userschoolgraph-data",this.schoolData)}});export{d as userschoolgraphadmin};