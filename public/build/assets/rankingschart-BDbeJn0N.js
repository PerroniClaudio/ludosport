const l=()=>{const r=new Date;return{events:[],selectedEvent:0,selectedEventData:{},athletesData:[],nationFilter:"",nation:[],eventName:"General Rankings",getEventsList:async function(s=null){const n=`/website-rankings/events/list?date=${r.toISOString()}${s?"&nation="+s:""}`,e=await fetch(n);if(e.ok){const a=await e.json();this.events=a}},getDataForEvent:async function(s){this.selectedEvent=s,this.athletesData=[];const n=`/website-rankings/events/${this.selectedEvent}/rankings`,e=await fetch(n);if(e.ok){const a=await e.json();let i=1;Object.entries(a).forEach(([t,o])=>{this.athletesData.push({id:t,name:o.user_name,rank:i++,battle_name:o.user_battle_name,academy:o.user_academy,school:o.user_school,school_slug:o.school_slug,nation:o.nation,war_points:o.total_war_points,style_points:o.total_style_points})})}this.rows=this.athletesData},getGeneralRankings:async function(){if(this.nationFilter!="")this.fiterByNation(this.nationFilter);else{this.selectedEvent=0,this.athletesData=[],this.eventName="General Rankings";const s=`/website-rankings/general?date=${r.toISOString()}`,n=await fetch(s);if(n.ok){const e=await n.json();let a=1;Object.entries(e).forEach(([i,t])=>{this.athletesData.push({id:i,name:t.user_name,rank:a++,battle_name:t.user_battle_name,academy:t.user_academy,school:t.user_school,school_slug:t.school_slug,nation:t.nation,war_points:t.total_war_points,style_points:t.total_style_points})})}this.rows=this.athletesData}},resetToGeneralRankings:function(){this.nationFilter="",this.getGeneralRankings(),this.getEventsList()},fiterByNation:async function(s){if(s==""){this.getGeneralRankings(),this.getEventsList();return}this.getEventsList(s),this.events=this.events.filter(i=>i.nation_id==s),this.athletesData=[];const e=await(await fetch(`/website-rankings/nation/${s}/rankings`)).json();let a=1;Object.entries(e.results).forEach(([i,t])=>{this.athletesData.push({id:i,name:t.user_name,rank:a++,battle_name:t.user_battle_name,academy:t.user_academy,school:t.user_school,school_slug:t.school_slug,nation:t.nation,war_points:t.total_war_points,style_points:t.total_style_points})}),this.eventName="National Rankings - "+e.nation.name,this.nation=e.nation,this.rows=this.athletesData,this.selectedEvent=0},columns:[{name:"Rank",field:"rank",columnClasses:""},{name:"Name",field:"name",columnClasses:""},{name:"Academy",field:"academy",columnClasses:""},{name:"School",field:"school",columnClasses:""},{name:"Nation",field:"nation",columnClasses:""},{name:"Arena Points",field:"war_points",columnClasses:""},{name:"Style Points",field:"style_points",columnClasses:""}],sortColumn:null,sortDirection:"asc",rows:[],sort:function(s){this.sortColumn===s?this.sortDirection=this.sortDirection==="asc"?"desc":"asc":(this.sortColumn=s,this.sortDirection="asc"),this.rows=[...this.rows].sort((n,e)=>{const a=this.columns[s],i=String(n[a.field]),t=String(e[a.field]);return this.sortDirection==="asc"?i.localeCompare(t):t.localeCompare(i)})},searchByValue:function(s){const n=s.target.value.toLowerCase();n===""?this.rows=athletesData:this.rows=athletesData.filter(e=>Object.values(e).some(a=>String(a).toLowerCase().includes(n)))},page:1,pageLength:10,totalPages:function(){return Math.ceil(this.rows.length/this.pageLength)},paginatedRows:function(){const s=(this.page-1)*this.pageLength,n=s+this.pageLength;return this.rows.slice(s,n)},init(){this.getGeneralRankings(),this.getEventsList()}}};export{l as rankingschart};
