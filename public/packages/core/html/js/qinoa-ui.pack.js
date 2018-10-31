/*! 
* date.js - v1.0 Alpha-1
* http://www.datejs.com/
* Copyright (c) 2006-2007 - Coolite Inc.; Licensed MIT licence */

/**
* Version: 1.0 Alpha-1
* Build Date: 13-Nov-2007
* Copyright (c) 2006-2007, Coolite Inc. (http://www.coolite.com/). All rights reserved.
* License: Licensed under The MIT License. See license.txt and http://www.datejs.com/license/.
* Website: http://www.datejs.com/ or http://www.coolite.com/datejs/
*/
Date.CultureInfo={name:"en-US",englishName:"English (United States)",nativeName:"English (United States)",dayNames:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],abbreviatedDayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],shortestDayNames:["Su","Mo","Tu","We","Th","Fr","Sa"],firstLetterDayNames:["S","M","T","W","T","F","S"],monthNames:["January","February","March","April","May","June","July","August","September","October","November","December"],abbreviatedMonthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],amDesignator:"AM",pmDesignator:"PM",firstDayOfWeek:0,twoDigitYearMax:2029,dateElementOrder:"mdy",formatPatterns:{shortDate:"M/d/yyyy",longDate:"dddd, MMMM dd, yyyy",shortTime:"h:mm tt",longTime:"h:mm:ss tt",fullDateTime:"dddd, MMMM dd, yyyy h:mm:ss tt",sortableDateTime:"yyyy-MM-ddTHH:mm:ss",universalSortableDateTime:"yyyy-MM-dd HH:mm:ssZ",rfc1123:"ddd, dd MMM yyyy HH:mm:ss GMT",monthDay:"MMMM dd",yearMonth:"MMMM, yyyy"},regexPatterns:{jan:/^jan(uary)?/i,feb:/^feb(ruary)?/i,mar:/^mar(ch)?/i,apr:/^apr(il)?/i,may:/^may/i,jun:/^jun(e)?/i,jul:/^jul(y)?/i,aug:/^aug(ust)?/i,sep:/^sep(t(ember)?)?/i,oct:/^oct(ober)?/i,nov:/^nov(ember)?/i,dec:/^dec(ember)?/i,sun:/^su(n(day)?)?/i,mon:/^mo(n(day)?)?/i,tue:/^tu(e(s(day)?)?)?/i,wed:/^we(d(nesday)?)?/i,thu:/^th(u(r(s(day)?)?)?)?/i,fri:/^fr(i(day)?)?/i,sat:/^sa(t(urday)?)?/i,future:/^next/i,past:/^last|past|prev(ious)?/i,add:/^(\+|after|from)/i,subtract:/^(\-|before|ago)/i,yesterday:/^yesterday/i,today:/^t(oday)?/i,tomorrow:/^tomorrow/i,now:/^n(ow)?/i,millisecond:/^ms|milli(second)?s?/i,second:/^sec(ond)?s?/i,minute:/^min(ute)?s?/i,hour:/^h(ou)?rs?/i,week:/^w(ee)?k/i,month:/^m(o(nth)?s?)?/i,day:/^d(ays?)?/i,year:/^y((ea)?rs?)?/i,shortMeridian:/^(a|p)/i,longMeridian:/^(a\.?m?\.?|p\.?m?\.?)/i,timezone:/^((e(s|d)t|c(s|d)t|m(s|d)t|p(s|d)t)|((gmt)?\s*(\+|\-)\s*\d\d\d\d?)|gmt)/i,ordinalSuffix:/^\s*(st|nd|rd|th)/i,timeContext:/^\s*(\:|a|p)/i},abbreviatedTimeZoneStandard:{GMT:"-000",EST:"-0400",CST:"-0500",MST:"-0600",PST:"-0700"},abbreviatedTimeZoneDST:{GMT:"-000",EDT:"-0500",CDT:"-0600",MDT:"-0700",PDT:"-0800"}};
Date.getMonthNumberFromName=function(name){var n=Date.CultureInfo.monthNames,m=Date.CultureInfo.abbreviatedMonthNames,s=name.toLowerCase();for(var i=0;i<n.length;i++){if(n[i].toLowerCase()==s||m[i].toLowerCase()==s){return i;}}
return-1;};Date.getDayNumberFromName=function(name){var n=Date.CultureInfo.dayNames,m=Date.CultureInfo.abbreviatedDayNames,o=Date.CultureInfo.shortestDayNames,s=name.toLowerCase();for(var i=0;i<n.length;i++){if(n[i].toLowerCase()==s||m[i].toLowerCase()==s){return i;}}
return-1;};Date.isLeapYear=function(year){return(((year%4===0)&&(year%100!==0))||(year%400===0));};Date.getDaysInMonth=function(year,month){return[31,(Date.isLeapYear(year)?29:28),31,30,31,30,31,31,30,31,30,31][month];};Date.getTimezoneOffset=function(s,dst){return(dst||false)?Date.CultureInfo.abbreviatedTimeZoneDST[s.toUpperCase()]:Date.CultureInfo.abbreviatedTimeZoneStandard[s.toUpperCase()];};Date.getTimezoneAbbreviation=function(offset,dst){var n=(dst||false)?Date.CultureInfo.abbreviatedTimeZoneDST:Date.CultureInfo.abbreviatedTimeZoneStandard,p;for(p in n){if(n[p]===offset){return p;}}
return null;};Date.prototype.clone=function(){return new Date(this.getTime());};Date.prototype.compareTo=function(date){if(isNaN(this)){throw new Error(this);}
if(date instanceof Date&&!isNaN(date)){return(this>date)?1:(this<date)?-1:0;}else{throw new TypeError(date);}};Date.prototype.equals=function(date){return(this.compareTo(date)===0);};Date.prototype.between=function(start,end){var t=this.getTime();return t>=start.getTime()&&t<=end.getTime();};Date.prototype.addMilliseconds=function(value){this.setMilliseconds(this.getMilliseconds()+value);return this;};Date.prototype.addSeconds=function(value){return this.addMilliseconds(value*1000);};Date.prototype.addMinutes=function(value){return this.addMilliseconds(value*60000);};Date.prototype.addHours=function(value){return this.addMilliseconds(value*3600000);};Date.prototype.addDays=function(value){return this.addMilliseconds(value*86400000);};Date.prototype.addWeeks=function(value){return this.addMilliseconds(value*604800000);};Date.prototype.addMonths=function(value){var n=this.getDate();this.setDate(1);this.setMonth(this.getMonth()+value);this.setDate(Math.min(n,this.getDaysInMonth()));return this;};Date.prototype.addYears=function(value){return this.addMonths(value*12);};Date.prototype.add=function(config){if(typeof config=="number"){this._orient=config;return this;}
var x=config;if(x.millisecond||x.milliseconds){this.addMilliseconds(x.millisecond||x.milliseconds);}
if(x.second||x.seconds){this.addSeconds(x.second||x.seconds);}
if(x.minute||x.minutes){this.addMinutes(x.minute||x.minutes);}
if(x.hour||x.hours){this.addHours(x.hour||x.hours);}
if(x.month||x.months){this.addMonths(x.month||x.months);}
if(x.year||x.years){this.addYears(x.year||x.years);}
if(x.day||x.days){this.addDays(x.day||x.days);}
return this;};Date._validate=function(value,min,max,name){if(typeof value!="number"){throw new TypeError(value+" is not a Number.");}else if(value<min||value>max){throw new RangeError(value+" is not a valid value for "+name+".");}
return true;};Date.validateMillisecond=function(n){return Date._validate(n,0,999,"milliseconds");};Date.validateSecond=function(n){return Date._validate(n,0,59,"seconds");};Date.validateMinute=function(n){return Date._validate(n,0,59,"minutes");};Date.validateHour=function(n){return Date._validate(n,0,23,"hours");};Date.validateDay=function(n,year,month){return Date._validate(n,1,Date.getDaysInMonth(year,month),"days");};Date.validateMonth=function(n){return Date._validate(n,0,11,"months");};Date.validateYear=function(n){return Date._validate(n,1,9999,"seconds");};Date.prototype.set=function(config){var x=config;if(!x.millisecond&&x.millisecond!==0){x.millisecond=-1;}
if(!x.second&&x.second!==0){x.second=-1;}
if(!x.minute&&x.minute!==0){x.minute=-1;}
if(!x.hour&&x.hour!==0){x.hour=-1;}
if(!x.day&&x.day!==0){x.day=-1;}
if(!x.month&&x.month!==0){x.month=-1;}
if(!x.year&&x.year!==0){x.year=-1;}
if(x.millisecond!=-1&&Date.validateMillisecond(x.millisecond)){this.addMilliseconds(x.millisecond-this.getMilliseconds());}
if(x.second!=-1&&Date.validateSecond(x.second)){this.addSeconds(x.second-this.getSeconds());}
if(x.minute!=-1&&Date.validateMinute(x.minute)){this.addMinutes(x.minute-this.getMinutes());}
if(x.hour!=-1&&Date.validateHour(x.hour)){this.addHours(x.hour-this.getHours());}
if(x.month!==-1&&Date.validateMonth(x.month)){this.addMonths(x.month-this.getMonth());}
if(x.year!=-1&&Date.validateYear(x.year)){this.addYears(x.year-this.getFullYear());}
if(x.day!=-1&&Date.validateDay(x.day,this.getFullYear(),this.getMonth())){this.addDays(x.day-this.getDate());}
if(x.timezone){this.setTimezone(x.timezone);}
if(x.timezoneOffset){this.setTimezoneOffset(x.timezoneOffset);}
return this;};Date.prototype.clearTime=function(){this.setHours(0);this.setMinutes(0);this.setSeconds(0);this.setMilliseconds(0);return this;};Date.prototype.isLeapYear=function(){var y=this.getFullYear();return(((y%4===0)&&(y%100!==0))||(y%400===0));};Date.prototype.isWeekday=function(){return!(this.is().sat()||this.is().sun());};Date.prototype.getDaysInMonth=function(){return Date.getDaysInMonth(this.getFullYear(),this.getMonth());};Date.prototype.moveToFirstDayOfMonth=function(){return this.set({day:1});};Date.prototype.moveToLastDayOfMonth=function(){return this.set({day:this.getDaysInMonth()});};Date.prototype.moveToDayOfWeek=function(day,orient){var diff=(day-this.getDay()+7*(orient||+1))%7;return this.addDays((diff===0)?diff+=7*(orient||+1):diff);};Date.prototype.moveToMonth=function(month,orient){var diff=(month-this.getMonth()+12*(orient||+1))%12;return this.addMonths((diff===0)?diff+=12*(orient||+1):diff);};Date.prototype.getDayOfYear=function(){return Math.floor((this-new Date(this.getFullYear(),0,1))/86400000);};Date.prototype.getWeekOfYear=function(firstDayOfWeek){var y=this.getFullYear(),m=this.getMonth(),d=this.getDate();var dow=firstDayOfWeek||Date.CultureInfo.firstDayOfWeek;var offset=7+1-new Date(y,0,1).getDay();if(offset==8){offset=1;}
var daynum=((Date.UTC(y,m,d,0,0,0)-Date.UTC(y,0,1,0,0,0))/86400000)+1;var w=Math.floor((daynum-offset+7)/7);if(w===dow){y--;var prevOffset=7+1-new Date(y,0,1).getDay();if(prevOffset==2||prevOffset==8){w=53;}else{w=52;}}
return w;};Date.prototype.isDST=function(){return this.toString().match(/(E|C|M|P)(S|D)T/)[2]=="D";};Date.prototype.getTimezone=function(){return Date.getTimezoneAbbreviation(this.getUTCOffset,this.isDST());};Date.prototype.setTimezoneOffset=function(s){var here=this.getTimezoneOffset(),there=Number(s)*-6/10;this.addMinutes(there-here);return this;};Date.prototype.setTimezone=function(s){return this.setTimezoneOffset(Date.getTimezoneOffset(s));};Date.prototype.getUTCOffset=function(){var n=this.getTimezoneOffset()*-10/6,r;if(n<0){r=(n-10000).toString();return r[0]+r.substr(2);}else{r=(n+10000).toString();return"+"+r.substr(1);}};Date.prototype.getDayName=function(abbrev){return abbrev?Date.CultureInfo.abbreviatedDayNames[this.getDay()]:Date.CultureInfo.dayNames[this.getDay()];};Date.prototype.getMonthName=function(abbrev){return abbrev?Date.CultureInfo.abbreviatedMonthNames[this.getMonth()]:Date.CultureInfo.monthNames[this.getMonth()];};Date.prototype._toString=Date.prototype.toString;Date.prototype.toString=function(format){var self=this;var p=function p(s){return(s.toString().length==1)?"0"+s:s;};return format?format.replace(/dd?d?d?|MM?M?M?|yy?y?y?|hh?|HH?|mm?|ss?|tt?|zz?z?/g,function(format){switch(format){case"hh":return p(self.getHours()<13?self.getHours():(self.getHours()-12));case"h":return self.getHours()<13?self.getHours():(self.getHours()-12);case"HH":return p(self.getHours());case"H":return self.getHours();case"mm":return p(self.getMinutes());case"m":return self.getMinutes();case"ss":return p(self.getSeconds());case"s":return self.getSeconds();case"yyyy":return self.getFullYear();case"yy":return self.getFullYear().toString().substring(2,4);case"dddd":return self.getDayName();case"ddd":return self.getDayName(true);case"dd":return p(self.getDate());case"d":return self.getDate().toString();case"MMMM":return self.getMonthName();case"MMM":return self.getMonthName(true);case"MM":return p((self.getMonth()+1));case"M":return self.getMonth()+1;case"t":return self.getHours()<12?Date.CultureInfo.amDesignator.substring(0,1):Date.CultureInfo.pmDesignator.substring(0,1);case"tt":return self.getHours()<12?Date.CultureInfo.amDesignator:Date.CultureInfo.pmDesignator;case"zzz":case"zz":case"z":return"";}}):this._toString();};
Date.now=function(){return new Date();};Date.today=function(){return Date.now().clearTime();};Date.prototype._orient=+1;Date.prototype.next=function(){this._orient=+1;return this;};Date.prototype.last=Date.prototype.prev=Date.prototype.previous=function(){this._orient=-1;return this;};Date.prototype._is=false;Date.prototype.is=function(){this._is=true;return this;};Number.prototype._dateElement="day";Number.prototype.fromNow=function(){var c={};c[this._dateElement]=this;return Date.now().add(c);};Number.prototype.ago=function(){var c={};c[this._dateElement]=this*-1;return Date.now().add(c);};(function(){var $D=Date.prototype,$N=Number.prototype;var dx=("sunday monday tuesday wednesday thursday friday saturday").split(/\s/),mx=("january february march april may june july august september october november december").split(/\s/),px=("Millisecond Second Minute Hour Day Week Month Year").split(/\s/),de;var df=function(n){return function(){if(this._is){this._is=false;return this.getDay()==n;}
return this.moveToDayOfWeek(n,this._orient);};};for(var i=0;i<dx.length;i++){$D[dx[i]]=$D[dx[i].substring(0,3)]=df(i);}
var mf=function(n){return function(){if(this._is){this._is=false;return this.getMonth()===n;}
return this.moveToMonth(n,this._orient);};};for(var j=0;j<mx.length;j++){$D[mx[j]]=$D[mx[j].substring(0,3)]=mf(j);}
var ef=function(j){return function(){if(j.substring(j.length-1)!="s"){j+="s";}
return this["add"+j](this._orient);};};var nf=function(n){return function(){this._dateElement=n;return this;};};for(var k=0;k<px.length;k++){de=px[k].toLowerCase();$D[de]=$D[de+"s"]=ef(px[k]);$N[de]=$N[de+"s"]=nf(de);}}());Date.prototype.toJSONString=function(){return this.toString("yyyy-MM-ddThh:mm:ssZ");};Date.prototype.toShortDateString=function(){return this.toString(Date.CultureInfo.formatPatterns.shortDatePattern);};Date.prototype.toLongDateString=function(){return this.toString(Date.CultureInfo.formatPatterns.longDatePattern);};Date.prototype.toShortTimeString=function(){return this.toString(Date.CultureInfo.formatPatterns.shortTimePattern);};Date.prototype.toLongTimeString=function(){return this.toString(Date.CultureInfo.formatPatterns.longTimePattern);};Date.prototype.getOrdinal=function(){switch(this.getDate()){case 1:case 21:case 31:return"st";case 2:case 22:return"nd";case 3:case 23:return"rd";default:return"th";}};
(function(){Date.Parsing={Exception:function(s){this.message="Parse error at '"+s.substring(0,10)+" ...'";}};var $P=Date.Parsing;var _=$P.Operators={rtoken:function(r){return function(s){var mx=s.match(r);if(mx){return([mx[0],s.substring(mx[0].length)]);}else{throw new $P.Exception(s);}};},token:function(s){return function(s){return _.rtoken(new RegExp("^\s*"+s+"\s*"))(s);};},stoken:function(s){return _.rtoken(new RegExp("^"+s));},until:function(p){return function(s){var qx=[],rx=null;while(s.length){try{rx=p.call(this,s);}catch(e){qx.push(rx[0]);s=rx[1];continue;}
break;}
return[qx,s];};},many:function(p){return function(s){var rx=[],r=null;while(s.length){try{r=p.call(this,s);}catch(e){return[rx,s];}
rx.push(r[0]);s=r[1];}
return[rx,s];};},optional:function(p){return function(s){var r=null;try{r=p.call(this,s);}catch(e){return[null,s];}
return[r[0],r[1]];};},not:function(p){return function(s){try{p.call(this,s);}catch(e){return[null,s];}
throw new $P.Exception(s);};},ignore:function(p){return p?function(s){var r=null;r=p.call(this,s);return[null,r[1]];}:null;},product:function(){var px=arguments[0],qx=Array.prototype.slice.call(arguments,1),rx=[];for(var i=0;i<px.length;i++){rx.push(_.each(px[i],qx));}
return rx;},cache:function(rule){var cache={},r=null;return function(s){try{r=cache[s]=(cache[s]||rule.call(this,s));}catch(e){r=cache[s]=e;}
if(r instanceof $P.Exception){throw r;}else{return r;}};},any:function(){var px=arguments;return function(s){var r=null;for(var i=0;i<px.length;i++){if(px[i]==null){continue;}
try{r=(px[i].call(this,s));}catch(e){r=null;}
if(r){return r;}}
throw new $P.Exception(s);};},each:function(){var px=arguments;return function(s){var rx=[],r=null;for(var i=0;i<px.length;i++){if(px[i]==null){continue;}
try{r=(px[i].call(this,s));}catch(e){throw new $P.Exception(s);}
rx.push(r[0]);s=r[1];}
return[rx,s];};},all:function(){var px=arguments,_=_;return _.each(_.optional(px));},sequence:function(px,d,c){d=d||_.rtoken(/^\s*/);c=c||null;if(px.length==1){return px[0];}
return function(s){var r=null,q=null;var rx=[];for(var i=0;i<px.length;i++){try{r=px[i].call(this,s);}catch(e){break;}
rx.push(r[0]);try{q=d.call(this,r[1]);}catch(ex){q=null;break;}
s=q[1];}
if(!r){throw new $P.Exception(s);}
if(q){throw new $P.Exception(q[1]);}
if(c){try{r=c.call(this,r[1]);}catch(ey){throw new $P.Exception(r[1]);}}
return[rx,(r?r[1]:s)];};},between:function(d1,p,d2){d2=d2||d1;var _fn=_.each(_.ignore(d1),p,_.ignore(d2));return function(s){var rx=_fn.call(this,s);return[[rx[0][0],r[0][2]],rx[1]];};},list:function(p,d,c){d=d||_.rtoken(/^\s*/);c=c||null;return(p instanceof Array?_.each(_.product(p.slice(0,-1),_.ignore(d)),p.slice(-1),_.ignore(c)):_.each(_.many(_.each(p,_.ignore(d))),px,_.ignore(c)));},set:function(px,d,c){d=d||_.rtoken(/^\s*/);c=c||null;return function(s){var r=null,p=null,q=null,rx=null,best=[[],s],last=false;for(var i=0;i<px.length;i++){q=null;p=null;r=null;last=(px.length==1);try{r=px[i].call(this,s);}catch(e){continue;}
rx=[[r[0]],r[1]];if(r[1].length>0&&!last){try{q=d.call(this,r[1]);}catch(ex){last=true;}}else{last=true;}
if(!last&&q[1].length===0){last=true;}
if(!last){var qx=[];for(var j=0;j<px.length;j++){if(i!=j){qx.push(px[j]);}}
p=_.set(qx,d).call(this,q[1]);if(p[0].length>0){rx[0]=rx[0].concat(p[0]);rx[1]=p[1];}}
if(rx[1].length<best[1].length){best=rx;}
if(best[1].length===0){break;}}
if(best[0].length===0){return best;}
if(c){try{q=c.call(this,best[1]);}catch(ey){throw new $P.Exception(best[1]);}
best[1]=q[1];}
return best;};},forward:function(gr,fname){return function(s){return gr[fname].call(this,s);};},replace:function(rule,repl){return function(s){var r=rule.call(this,s);return[repl,r[1]];};},process:function(rule,fn){return function(s){var r=rule.call(this,s);return[fn.call(this,r[0]),r[1]];};},min:function(min,rule){return function(s){var rx=rule.call(this,s);if(rx[0].length<min){throw new $P.Exception(s);}
return rx;};}};var _generator=function(op){return function(){var args=null,rx=[];if(arguments.length>1){args=Array.prototype.slice.call(arguments);}else if(arguments[0]instanceof Array){args=arguments[0];}
if(args){for(var i=0,px=args.shift();i<px.length;i++){args.unshift(px[i]);rx.push(op.apply(null,args));args.shift();return rx;}}else{return op.apply(null,arguments);}};};var gx="optional not ignore cache".split(/\s/);for(var i=0;i<gx.length;i++){_[gx[i]]=_generator(_[gx[i]]);}
var _vector=function(op){return function(){if(arguments[0]instanceof Array){return op.apply(null,arguments[0]);}else{return op.apply(null,arguments);}};};var vx="each any all".split(/\s/);for(var j=0;j<vx.length;j++){_[vx[j]]=_vector(_[vx[j]]);}}());(function(){var flattenAndCompact=function(ax){var rx=[];for(var i=0;i<ax.length;i++){if(ax[i]instanceof Array){rx=rx.concat(flattenAndCompact(ax[i]));}else{if(ax[i]){rx.push(ax[i]);}}}
return rx;};Date.Grammar={};Date.Translator={hour:function(s){return function(){this.hour=Number(s);};},minute:function(s){return function(){this.minute=Number(s);};},second:function(s){return function(){this.second=Number(s);};},meridian:function(s){return function(){this.meridian=s.slice(0,1).toLowerCase();};},timezone:function(s){return function(){var n=s.replace(/[^\d\+\-]/g,"");if(n.length){this.timezoneOffset=Number(n);}else{this.timezone=s.toLowerCase();}};},day:function(x){var s=x[0];return function(){this.day=Number(s.match(/\d+/)[0]);};},month:function(s){return function(){this.month=((s.length==3)?Date.getMonthNumberFromName(s):(Number(s)-1));};},year:function(s){return function(){var n=Number(s);this.year=((s.length>2)?n:(n+(((n+2000)<Date.CultureInfo.twoDigitYearMax)?2000:1900)));};},rday:function(s){return function(){switch(s){case"yesterday":this.days=-1;break;case"tomorrow":this.days=1;break;case"today":this.days=0;break;case"now":this.days=0;this.now=true;break;}};},finishExact:function(x){x=(x instanceof Array)?x:[x];var now=new Date();this.year=now.getFullYear();this.month=now.getMonth();this.day=1;this.hour=0;this.minute=0;this.second=0;for(var i=0;i<x.length;i++){if(x[i]){x[i].call(this);}}
this.hour=(this.meridian=="p"&&this.hour<13)?this.hour+12:this.hour;if(this.day>Date.getDaysInMonth(this.year,this.month)){throw new RangeError(this.day+" is not a valid value for days.");}
var r=new Date(this.year,this.month,this.day,this.hour,this.minute,this.second);if(this.timezone){r.set({timezone:this.timezone});}else if(this.timezoneOffset){r.set({timezoneOffset:this.timezoneOffset});}
return r;},finish:function(x){x=(x instanceof Array)?flattenAndCompact(x):[x];if(x.length===0){return null;}
for(var i=0;i<x.length;i++){if(typeof x[i]=="function"){x[i].call(this);}}
if(this.now){return new Date();}
var today=Date.today();var method=null;var expression=!!(this.days!=null||this.orient||this.operator);if(expression){var gap,mod,orient;orient=((this.orient=="past"||this.operator=="subtract")?-1:1);if(this.weekday){this.unit="day";gap=(Date.getDayNumberFromName(this.weekday)-today.getDay());mod=7;this.days=gap?((gap+(orient*mod))%mod):(orient*mod);}
if(this.month){this.unit="month";gap=(this.month-today.getMonth());mod=12;this.months=gap?((gap+(orient*mod))%mod):(orient*mod);this.month=null;}
if(!this.unit){this.unit="day";}
if(this[this.unit+"s"]==null||this.operator!=null){if(!this.value){this.value=1;}
if(this.unit=="week"){this.unit="day";this.value=this.value*7;}
this[this.unit+"s"]=this.value*orient;}
return today.add(this);}else{if(this.meridian&&this.hour){this.hour=(this.hour<13&&this.meridian=="p")?this.hour+12:this.hour;}
if(this.weekday&&!this.day){this.day=(today.addDays((Date.getDayNumberFromName(this.weekday)-today.getDay()))).getDate();}
if(this.month&&!this.day){this.day=1;}
return today.set(this);}}};var _=Date.Parsing.Operators,g=Date.Grammar,t=Date.Translator,_fn;g.datePartDelimiter=_.rtoken(/^([\s\-\.\,\/\x27]+)/);g.timePartDelimiter=_.stoken(":");g.whiteSpace=_.rtoken(/^\s*/);g.generalDelimiter=_.rtoken(/^(([\s\,]|at|on)+)/);var _C={};g.ctoken=function(keys){var fn=_C[keys];if(!fn){var c=Date.CultureInfo.regexPatterns;var kx=keys.split(/\s+/),px=[];for(var i=0;i<kx.length;i++){px.push(_.replace(_.rtoken(c[kx[i]]),kx[i]));}
fn=_C[keys]=_.any.apply(null,px);}
return fn;};g.ctoken2=function(key){return _.rtoken(Date.CultureInfo.regexPatterns[key]);};g.h=_.cache(_.process(_.rtoken(/^(0[0-9]|1[0-2]|[1-9])/),t.hour));g.hh=_.cache(_.process(_.rtoken(/^(0[0-9]|1[0-2])/),t.hour));g.H=_.cache(_.process(_.rtoken(/^([0-1][0-9]|2[0-3]|[0-9])/),t.hour));g.HH=_.cache(_.process(_.rtoken(/^([0-1][0-9]|2[0-3])/),t.hour));g.m=_.cache(_.process(_.rtoken(/^([0-5][0-9]|[0-9])/),t.minute));g.mm=_.cache(_.process(_.rtoken(/^[0-5][0-9]/),t.minute));g.s=_.cache(_.process(_.rtoken(/^([0-5][0-9]|[0-9])/),t.second));g.ss=_.cache(_.process(_.rtoken(/^[0-5][0-9]/),t.second));g.hms=_.cache(_.sequence([g.H,g.mm,g.ss],g.timePartDelimiter));g.t=_.cache(_.process(g.ctoken2("shortMeridian"),t.meridian));g.tt=_.cache(_.process(g.ctoken2("longMeridian"),t.meridian));g.z=_.cache(_.process(_.rtoken(/^(\+|\-)?\s*\d\d\d\d?/),t.timezone));g.zz=_.cache(_.process(_.rtoken(/^(\+|\-)\s*\d\d\d\d/),t.timezone));g.zzz=_.cache(_.process(g.ctoken2("timezone"),t.timezone));g.timeSuffix=_.each(_.ignore(g.whiteSpace),_.set([g.tt,g.zzz]));g.time=_.each(_.optional(_.ignore(_.stoken("T"))),g.hms,g.timeSuffix);g.d=_.cache(_.process(_.each(_.rtoken(/^([0-2]\d|3[0-1]|\d)/),_.optional(g.ctoken2("ordinalSuffix"))),t.day));g.dd=_.cache(_.process(_.each(_.rtoken(/^([0-2]\d|3[0-1])/),_.optional(g.ctoken2("ordinalSuffix"))),t.day));g.ddd=g.dddd=_.cache(_.process(g.ctoken("sun mon tue wed thu fri sat"),function(s){return function(){this.weekday=s;};}));g.M=_.cache(_.process(_.rtoken(/^(1[0-2]|0\d|\d)/),t.month));g.MM=_.cache(_.process(_.rtoken(/^(1[0-2]|0\d)/),t.month));g.MMM=g.MMMM=_.cache(_.process(g.ctoken("jan feb mar apr may jun jul aug sep oct nov dec"),t.month));g.y=_.cache(_.process(_.rtoken(/^(\d\d?)/),t.year));g.yy=_.cache(_.process(_.rtoken(/^(\d\d)/),t.year));g.yyy=_.cache(_.process(_.rtoken(/^(\d\d?\d?\d?)/),t.year));g.yyyy=_.cache(_.process(_.rtoken(/^(\d\d\d\d)/),t.year));_fn=function(){return _.each(_.any.apply(null,arguments),_.not(g.ctoken2("timeContext")));};g.day=_fn(g.d,g.dd);g.month=_fn(g.M,g.MMM);g.year=_fn(g.yyyy,g.yy);g.orientation=_.process(g.ctoken("past future"),function(s){return function(){this.orient=s;};});g.operator=_.process(g.ctoken("add subtract"),function(s){return function(){this.operator=s;};});g.rday=_.process(g.ctoken("yesterday tomorrow today now"),t.rday);g.unit=_.process(g.ctoken("minute hour day week month year"),function(s){return function(){this.unit=s;};});g.value=_.process(_.rtoken(/^\d\d?(st|nd|rd|th)?/),function(s){return function(){this.value=s.replace(/\D/g,"");};});g.expression=_.set([g.rday,g.operator,g.value,g.unit,g.orientation,g.ddd,g.MMM]);_fn=function(){return _.set(arguments,g.datePartDelimiter);};g.mdy=_fn(g.ddd,g.month,g.day,g.year);g.ymd=_fn(g.ddd,g.year,g.month,g.day);g.dmy=_fn(g.ddd,g.day,g.month,g.year);g.date=function(s){return((g[Date.CultureInfo.dateElementOrder]||g.mdy).call(this,s));};g.format=_.process(_.many(_.any(_.process(_.rtoken(/^(dd?d?d?|MM?M?M?|yy?y?y?|hh?|HH?|mm?|ss?|tt?|zz?z?)/),function(fmt){if(g[fmt]){return g[fmt];}else{throw Date.Parsing.Exception(fmt);}}),_.process(_.rtoken(/^[^dMyhHmstz]+/),function(s){return _.ignore(_.stoken(s));}))),function(rules){return _.process(_.each.apply(null,rules),t.finishExact);});var _F={};var _get=function(f){return _F[f]=(_F[f]||g.format(f)[0]);};g.formats=function(fx){if(fx instanceof Array){var rx=[];for(var i=0;i<fx.length;i++){rx.push(_get(fx[i]));}
return _.any.apply(null,rx);}else{return _get(fx);}};g._formats=g.formats(["yyyy-MM-ddTHH:mm:ss","ddd, MMM dd, yyyy H:mm:ss tt","ddd MMM d yyyy HH:mm:ss zzz","d"]);g._start=_.process(_.set([g.date,g.time,g.expression],g.generalDelimiter,g.whiteSpace),t.finish);g.start=function(s){try{var r=g._formats.call({},s);if(r[1].length===0){return r;}}catch(e){}
return g._start.call({},s);};}());Date._parse=Date.parse;Date.parse=function(s){var r=null;if(!s){return null;}
try{r=Date.Grammar.start.call({},s);}catch(e){return null;}
return((r[1].length===0)?r[0]:null);};Date.getParseFunction=function(fx){var fn=Date.Grammar.formats(fx);return function(s){var r=null;try{r=fn.call({},s);}catch(e){return null;}
return((r[1].length===0)?r[0]:null);};};Date.parseExact=function(s,fx){return Date.getParseFunction(fx)(s);};


/**
* version: 1.0 Alpha-1
* author: Coolite Inc. http://www.coolite.com/
* date: 2008-04-13
* copyright: Copyright (c) 2006-2008, Coolite Inc. (http://www.coolite.com/). All rights reserved.
* license: Licensed under The MIT License. See license.txt and http://www.datejs.com/license/.
* website: http://www.datejs.com/
*/
 
/*
* TimeSpan(milliseconds);
* TimeSpan(days, hours, minutes, seconds);
* TimeSpan(days, hours, minutes, seconds, milliseconds);
*/
var TimeSpan = function (days, hours, minutes, seconds, milliseconds) {
var attrs = "days hours minutes seconds milliseconds".split(/\s+/);

var gFn = function (attr) {
return function () {
return this[attr];
};
};

var sFn = function (attr) {
return function (val) {
this[attr] = val;
return this;
};
};

for (var i = 0; i < attrs.length ; i++) {
var $a = attrs[i], $b = $a.slice(0, 1).toUpperCase() + $a.slice(1);
TimeSpan.prototype[$a] = 0;
TimeSpan.prototype["get" + $b] = gFn($a);
TimeSpan.prototype["set" + $b] = sFn($a);
}

if (arguments.length == 4) {
this.setDays(days);
this.setHours(hours);
this.setMinutes(minutes);
this.setSeconds(seconds);
} else if (arguments.length == 5) {
this.setDays(days);
this.setHours(hours);
this.setMinutes(minutes);
this.setSeconds(seconds);
this.setMilliseconds(milliseconds);
} else if (arguments.length == 1 && typeof days == "number") {
var orient = (days < 0) ? -1 : +1;
this.setMilliseconds(Math.abs(days));

this.setDays(Math.floor(this.getMilliseconds() / 86400000) * orient);
this.setMilliseconds(this.getMilliseconds() % 86400000);

this.setHours(Math.floor(this.getMilliseconds() / 3600000) * orient);
this.setMilliseconds(this.getMilliseconds() % 3600000);

this.setMinutes(Math.floor(this.getMilliseconds() / 60000) * orient);
this.setMilliseconds(this.getMilliseconds() % 60000);

this.setSeconds(Math.floor(this.getMilliseconds() / 1000) * orient);
this.setMilliseconds(this.getMilliseconds() % 1000);

this.setMilliseconds(this.getMilliseconds() * orient);
}

this.getTotalMilliseconds = function () {
return (this.getDays() * 86400000) + (this.getHours() * 3600000) + (this.getMinutes() * 60000) + (this.getSeconds() * 1000);
};

this.compareTo = function (time) {
var t1 = new Date(1970, 1, 1, this.getHours(), this.getMinutes(), this.getSeconds()), t2;
if (time === null) {
t2 = new Date(1970, 1, 1, 0, 0, 0);
}
else {
t2 = new Date(1970, 1, 1, time.getHours(), time.getMinutes(), time.getSeconds());
}
return (t1 < t2) ? -1 : (t1 > t2) ? 1 : 0;
};

this.equals = function (time) {
return (this.compareTo(time) === 0);
};

this.add = function (time) {
return (time === null) ? this : this.addSeconds(time.getTotalMilliseconds() / 1000);
};

this.subtract = function (time) {
return (time === null) ? this : this.addSeconds(-time.getTotalMilliseconds() / 1000);
};

this.addDays = function (n) {
return new TimeSpan(this.getTotalMilliseconds() + (n * 86400000));
};

this.addHours = function (n) {
return new TimeSpan(this.getTotalMilliseconds() + (n * 3600000));
};

this.addMinutes = function (n) {
return new TimeSpan(this.getTotalMilliseconds() + (n * 60000));
};

this.addSeconds = function (n) {
return new TimeSpan(this.getTotalMilliseconds() + (n * 1000));
};

this.addMilliseconds = function (n) {
return new TimeSpan(this.getTotalMilliseconds() + n);
};

this.get12HourHour = function () {
return (this.getHours() > 12) ? this.getHours() - 12 : (this.getHours() === 0) ? 12 : this.getHours();
};

this.getDesignator = function () {
return (this.getHours() < 12) ? Date.CultureInfo.amDesignator : Date.CultureInfo.pmDesignator;
};

this.toString = function (format) {
this._toString = function () {
if (this.getDays() !== null && this.getDays() > 0) {
return this.getDays() + "." + this.getHours() + ":" + this.p(this.getMinutes()) + ":" + this.p(this.getSeconds());
}
else {
return this.getHours() + ":" + this.p(this.getMinutes()) + ":" + this.p(this.getSeconds());
}
};

this.p = function (s) {
return (s.toString().length < 2) ? "0" + s : s;
};

var me = this;

return format ? format.replace(/dd?|HH?|hh?|mm?|ss?|tt?/g,
function (format) {
switch (format) {
case "d":
return me.getDays();
case "dd":
return me.p(me.getDays());
case "H":
return me.getHours();
case "HH":
return me.p(me.getHours());
case "h":
return me.get12HourHour();
case "hh":
return me.p(me.get12HourHour());
case "m":
return me.getMinutes();
case "mm":
return me.p(me.getMinutes());
case "s":
return me.getSeconds();
case "ss":
return me.p(me.getSeconds());
case "t":
return ((me.getHours() < 12) ? Date.CultureInfo.amDesignator : Date.CultureInfo.pmDesignator).substring(0, 1);
case "tt":
return (me.getHours() < 12) ? Date.CultureInfo.amDesignator : Date.CultureInfo.pmDesignator;
}
}
) : this._toString();
};
return this;
};

/**
* Gets the time of day for this date instances.
* @return {TimeSpan} TimeSpan
*/
Date.prototype.getTimeOfDay = function () {
return new TimeSpan(0, this.getHours(), this.getMinutes(), this.getSeconds(), this.getMilliseconds());
};

/*
* TimePeriod(startDate, endDate);
* TimePeriod(years, months, days, hours, minutes, seconds, milliseconds);
*/
var TimePeriod = function (years, months, days, hours, minutes, seconds, milliseconds) {
var attrs = "years months days hours minutes seconds milliseconds".split(/\s+/);

var gFn = function (attr) {
return function () {
return this[attr];
};
};

var sFn = function (attr) {
return function (val) {
this[attr] = val;
return this;
};
};

for (var i = 0; i < attrs.length ; i++) {
var $a = attrs[i], $b = $a.slice(0, 1).toUpperCase() + $a.slice(1);
TimePeriod.prototype[$a] = 0;
TimePeriod.prototype["get" + $b] = gFn($a);
TimePeriod.prototype["set" + $b] = sFn($a);
}

if (arguments.length == 7) {
this.years = years;
this.months = months;
this.setDays(days);
this.setHours(hours);
this.setMinutes(minutes);
this.setSeconds(seconds);
this.setMilliseconds(milliseconds);
} else if (arguments.length == 2 && arguments[0] instanceof Date && arguments[1] instanceof Date) {
// startDate and endDate as arguments

var d1 = years.clone();
var d2 = months.clone();

var temp = d1.clone();
var orient = (d1 > d2) ? -1 : +1;

this.years = d2.getFullYear() - d1.getFullYear();
temp.addYears(this.years);

if (orient == +1) {
if (temp > d2) {
if (this.years !== 0) {
this.years--;
}
}
} else {
if (temp < d2) {
if (this.years !== 0) {
this.years++;
}
}
}

d1.addYears(this.years);

if (orient == +1) {
while (d1 < d2 && d1.clone().addDays(Date.getDaysInMonth(d1.getYear(), d1.getMonth()) ) < d2) {
d1.addMonths(1);
this.months++;
}
}
else {
while (d1 > d2 && d1.clone().addDays(-d1.getDaysInMonth()) > d2) {
d1.addMonths(-1);
this.months--;
}
}

var diff = d2 - d1;

if (diff !== 0) {
var ts = new TimeSpan(diff);
this.setDays(ts.getDays());
this.setHours(ts.getHours());
this.setMinutes(ts.getMinutes());
this.setSeconds(ts.getSeconds());
this.setMilliseconds(ts.getMilliseconds());
}
}
return this;
};

/*! 
* evol.colorpicker - v3.2.0
* https://github.com/evoluteur/colorpicker
* Copyright (c) 2015 - Olivier Giulieri; Licensed MIT licence */

/*
 evol.colorpicker 3.2.0
 ColorPicker widget for jQuery UI

 https://github.com/evoluteur/colorpicker
 (c) 2015 Olivier Giulieri

 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 */

(function( $, undefined ) {

var _idx=0,
	ua=window.navigator.userAgent,
	isIE=ua.indexOf("MSIE ")>0,
	_ie=isIE?'-ie':'',
	isMoz=isIE?false:/mozilla/.test(ua.toLowerCase()) && !/webkit/.test(ua.toLowerCase()),
	history=[],
	baseThemeColors=['ffffff','000000','eeece1','1f497d','4f81bd','c0504d','9bbb59','8064a2','4bacc6','f79646'],
	subThemeColors=['f2f2f2','7f7f7f','ddd9c3','c6d9f0','dbe5f1','f2dcdb','ebf1dd','e5e0ec','dbeef3','fdeada',
		'd8d8d8','595959','c4bd97','8db3e2','b8cce4','e5b9b7','d7e3bc','ccc1d9','b7dde8','fbd5b5',
		'bfbfbf','3f3f3f','938953','548dd4','95b3d7','d99694','c3d69b','b2a2c7','92cddc','fac08f',
		'a5a5a5','262626','494429','17365d','366092','953734','76923c','5f497a','31859b','e36c09',
		'7f7f7f','0c0c0c','1d1b10','0f243e','244061','632423','4f6128','3f3151','205867','974806'],
	standardColors=['c00000','ff0000','ffc000','ffff00','92d050','00b050','00b0f0','0070c0','002060','7030a0'],
	webColors=[
		['003366','336699','3366cc','003399','000099','0000cc','000066'],
		['006666','006699','0099cc','0066cc','0033cc','0000ff','3333ff','333399'],
		['669999','009999','33cccc','00ccff','0099ff','0066ff','3366ff','3333cc','666699'],
		['339966','00cc99','00ffcc','00ffff','33ccff','3399ff','6699ff','6666ff','6600ff','6600cc'],
		['339933','00cc66','00ff99','66ffcc','66ffff','66ccff','99ccff','9999ff','9966ff','9933ff','9900ff'],
		['006600','00cc00','00ff00','66ff99','99ffcc','ccffff','ccccff','cc99ff','cc66ff','cc33ff','cc00ff','9900cc'],
		['003300','009933','33cc33','66ff66','99ff99','ccffcc','ffffff','ffccff','ff99ff','ff66ff','ff00ff','cc00cc','660066'],
		['333300','009900','66ff33','99ff66','ccff99','ffffcc','ffcccc','ff99cc','ff66cc','ff33cc','cc0099','993399'],
		['336600','669900','99ff33','ccff66','ffff99','ffcc99','ff9999','ff6699','ff3399','cc3399','990099'],
		['666633','99cc00','ccff33','ffff66','ffcc66','ff9966','ff6666','ff0066','d60094','993366'],
		['a58800','cccc00','ffff00','ffcc00','ff9933','ff6600','ff0033','cc0066','660033'],
		['996633','cc9900','ff9900','cc6600','ff3300','ff0000','cc0000','990033'],
		['663300','996600','cc3300','993300','990000','800000','993333']
	],
	transColor='#0000ffff',
	int2Hex=function(i){
		var h=i.toString(16);
		if(h.length==1){
			h='0'+h;
		}
		return h;
	},
	st2Hex=function(s){
		return int2Hex(Number(s));
	},
	int2Hex3=function(i){
		var h=int2Hex(i);
		return h+h+h;
	},
	toHex3=function(c){
		if(c.length>10){ // IE9
			var p1=1+c.indexOf('('),
				p2=c.indexOf(')'),
				cs=c.substring(p1,p2).split(',');
			return ['#',st2Hex(cs[0]),st2Hex(cs[1]),st2Hex(cs[2])].join('');
		}else{
			return c;
		}
	};

$.widget( "evol.colorpicker", {

	version: '3.2.0',
	
	options: {
		color: null, // example:'#31859B'
		showOn: 'both', // possible values: 'focus','button','both'
		hideButton: false,
		displayIndicator: true,
		transparentColor: false,
		history: true,
		defaultPalette: 'theme', // possible values: 'theme', 'web'
		strings: 'Theme Colors,Standard Colors,Web Colors,Theme Colors,Back to Palette,History,No history yet.'
	},

	_create: function() {
		var that=this;
		this._paletteIdx=this.options.defaultPalette=='theme'?1:2;
		this._id='evo-cp'+_idx++;
		this._enabled=true;
		this.options.showOn=this.options.hideButton?'focus':this.options.showOn;
		switch(this.element.get(0).tagName){
			case 'INPUT':
				var color=this.options.color,
					e=this.element,
					css=((this.options.showOn==='focus')?'':'evo-pointer ')+'evo-colorind'+(isMoz?'-ff':_ie)+(this.options.hideButton?' evo-hidden-button':''),
					style='';
				this._isPopup=true;
				this._palette=null;
				if(color!==null){
					e.val(color);
				}else{
					var v=e.val();
					if(v!==''){
						color=this.options.color=v;
					}
				}
				if(color===transColor){
					css+=' evo-transparent';
				}else{
					style=(color!==null)?('background-color:'+color):'';
				}
				e.addClass('colorPicker '+this._id)
					.wrap('<div style="width:'+(this.options.hideButton?this.element.width():this.element.width()+32)+'px;'+
						(isIE?'margin-bottom:-21px;':'')+
						(isMoz?'padding:1px 0;':'')+
						'"></div>')
					.after('<div class="'+css+'" style="'+style+'"></div>')
					.on('keyup onpaste', function(evt){
						var c=$(this).val();
						if(c!=that.options.color){
							that._setValue(c, true);
						}
					});
				var showOn=this.options.showOn;
				if(showOn==='both' || showOn==='focus'){
					e.on('focus', function(){
						that.showPalette();
					});
				}
				if(showOn==='both' || showOn==='button'){
					e.next().on('click', function(evt){
						evt.stopPropagation();
						that.showPalette();
					});
				}
				break;
			default:
				this._isPopup=false;
				this._palette=this.element.html(this._paletteHTML())
					.attr('aria-haspopup','true');
				this._bindColors();
		}
		if(color && this.options.history){
			this._add2History(color);
		}
	},

	_paletteHTML: function() {
		var pIdx=this._paletteIdx=Math.abs(this._paletteIdx),
			opts=this.options,
			labels=opts.strings.split(',');

		var h='<div class="evo-pop'+_ie+' ui-widget ui-widget-content ui-corner-all"'+
			(this._isPopup?' style="position:absolute"':'')+'>'+
			// palette
			'<span>'+this['_paletteHTML'+pIdx]()+'</span>'+
			// links
			'<div class="evo-more"><a href="javascript:void(0)">'+labels[1+pIdx]+'</a>';
		if(opts.history){
			h+='<a href="javascript:void(0)" class="evo-hist">'+labels[5]+'</a>';
		}
		h+='</div>';
		// indicator
		if(opts.displayIndicator){
			h+=this._colorIndHTML(this.options.color)+this._colorIndHTML('');
		}
		h+='</div>';
		return h;
	},

	_colorIndHTML: function(c) {
		var css=isIE?'evo-colorbox-ie ':'',
			style='';

		if(c){
			if(c===transColor){
				css+='evo-transparent';
			}else{
				style='background-color:'+c;
			}
		}else{
			style='display:none';
		}
		return '<div class="evo-color" style="float:left">'+
			'<div style="'+style+'" class="'+css+'"></div><span>'+ // class="evo-colortxt-ie"
			(c?c:'')+'</span></div>';
	},

	_paletteHTML1: function() {
		var opts=this.options,
			labels=opts.strings.split(','),
			oTD='<td style="background-color:#',
			cTD=isIE?'"><div style="width:2px;"></div></td>':'"><span/></td>',
			oTRTH='<tr><th colspan="10" class="ui-widget-content">';

		// base theme colors
		var h='<table class="evo-palette'+_ie+'">'+oTRTH+labels[0]+'</th></tr><tr>';
		for(var i=0;i<10;i++){ 
			h+=oTD+baseThemeColors[i]+cTD;
		}
		h+='</tr>';
		if(!isIE){
			h+='<tr><th colspan="10"></th></tr>';
		}
		h+='<tr class="top">';
		// theme colors
		for(i=0;i<10;i++){ 
			h+=oTD+subThemeColors[i]+cTD;
		}
		for(var r=1;r<4;r++){
			h+='</tr><tr class="in">';
			for(i=0;i<10;i++){ 
				h+=oTD+subThemeColors[r*10+i]+cTD;
			}
		}
		h+='</tr><tr class="bottom">';
		for(i=40;i<50;i++){ 
			h+=oTD+subThemeColors[i]+cTD;
		}
		h+='</tr>'+oTRTH;
		// transparent color
		if(opts.transparentColor){
			h+='<div class="evo-transparent evo-tr-box"></div>';
		}
		h+=labels[1]+'</th></tr><tr>';
		// standard colors
		for(i=0;i<10;i++){ 
			h+=oTD+standardColors[i]+cTD;
		}
		h+='</tr></table>';
		return h; 
	},

	_paletteHTML2: function() {
		var i, iMax,
			oTD='<td style="background-color:#',
			cTD=isIE?'"><div style="width:5px;"></div></td>':'"><span/></td>',
			oTableTR='<table class="evo-palette2'+_ie+'"><tr>',
			cTableTR='</tr></table>';

		var h='<div class="evo-palcenter">';
		// hexagon colors
		for(var r=0,rMax=webColors.length;r<rMax;r++){
			h+=oTableTR;
			var cs=webColors[r];
			for(i=0,iMax=cs.length;i<iMax;i++){ 
				h+=oTD+cs[i]+cTD;
			}
			h+=cTableTR;
		}
		h+='<div class="evo-sep"/>';
		// gray scale colors
		var h2='';
		h+=oTableTR;
		for(i=255;i>10;i-=10){
			h+=oTD+int2Hex3(i)+cTD;
			i-=10;
			h2+=oTD+int2Hex3(i)+cTD;
		}
		h+=cTableTR+oTableTR+h2+cTableTR+'</div>';
		return h;
	},

	_switchPalette: function(link) {
		if(this._enabled){
			var idx, 
				content, 
				label,
				labels=this.options.strings.split(',');
			if($(link).hasClass('evo-hist')){
				// history
				var h=['<table class="evo-palette"><tr><th class="ui-widget-content">',
					labels[5], '</th></tr></tr></table>',
					'<div class="evo-cHist">'];
				if(history.length===0){
					h.push('<p>&nbsp;',labels[6],'</p>');
				}else{
					for(var i=history.length-1;i>-1;i--){
						if(history[i].length===9){
							h.push('<div class="evo-transparent"></div>');
						}else{
							h.push('<div style="background-color:',history[i],'"></div>');
						}
					}
				}
				h.push('</div>');
				idx=-this._paletteIdx;
				content=h.join('');
				label=labels[4];
			}else{
				// palette
				if(this._paletteIdx<0){
					idx=-this._paletteIdx;
					this._palette.find('.evo-hist').show();
				}else{
					idx=(this._paletteIdx==2)?1:2;
				}
				content=this['_paletteHTML'+idx]();
				label=labels[idx+1];
				this._paletteIdx=idx;
			}
			this._paletteIdx=idx;
			var e=this._palette.find('.evo-more')
				.prev().html(content).end()
				.children().eq(0).html(label);
			if(idx<0){
				e.next().hide();
			}
		}
	},

	showPalette: function() {
		if(this._enabled){
			$('.colorPicker').not('.'+this._id).colorpicker('hidePalette');
			if(this._palette===null){
				this._palette=this.element.next()
					.after(this._paletteHTML()).next()
					.on('click',function(evt){
						evt.stopPropagation();
					});
				this._bindColors();
				var that=this;
				if(this._isPopup){
					$(document.body).on('click.'+that._id, function(evt){
						if(evt.target!=that.element.get(0)){
							that.hidePalette();
						}
					}).on('keyup.'+that._id, function(evt){
						if(evt.keyCode===27){
							that.hidePalette();
						}
					});
				}
			}
		}
		return this;
	},

	hidePalette: function() {
		if(this._isPopup && this._palette){
			$(document.body).off('click.'+this._id);
			var that=this;
			this._palette.off('mouseover click', 'td,.evo-transparent')
				.fadeOut(function(){
					that._palette.remove();
					that._palette=that._cTxt=null;
				})
				.find('.evo-more a').off('click');
		}
		return this;
	},

	_bindColors: function() {
		var that=this,
			opts=this.options,
			es=this._palette.find('div.evo-color'),
			sel=opts.history?'td,.evo-cHist>div':'td';

		if(opts.transparentColor){
			sel+=',.evo-transparent';
		}
		this._cTxt1=es.eq(0).children().eq(0);
		this._cTxt2=es.eq(1).children().eq(0);
		this._palette
			.on('click', sel, function(evt){
				if(that._enabled){
					var $this=$(this);
					that._setValue($this.hasClass('evo-transparent')?transColor:toHex3($this.attr('style').substring(17)));
				}
			})
			.on('mouseover', sel, function(evt){
				if(that._enabled){
					var $this=$(this),
						c=$this.hasClass('evo-transparent')?transColor:toHex3($this.attr('style').substring(17));
					if(that.options.displayIndicator){
						that._setColorInd(c,2);
					}
					that.element.trigger('mouseover.color', c);
				}
			})
			.find('.evo-more a').on('click', function(){
				that._switchPalette(this);
			});
	},

	val: function(value) {
		if (typeof value=='undefined') {
			return this.options.color;
		}else{
			this._setValue(value);
			return this;
		}
	},

	_setValue: function(c, noHide) {
		c = c.replace(/ /g,'');
		this.options.color=c;
		if(this._isPopup){
			if(!noHide){
				this.hidePalette();
			}
			this._setBoxColor(this.element.val(c).next(), c);
		}else{
			this._setColorInd(c,1);
		}
		if(this.options.history && this._paletteIdx>0){
			this._add2History(c);
		}
		this.element.trigger('change.color', c);
	},

	_setColorInd: function(c, idx) {
		var $box=this['_cTxt'+idx];
		this._setBoxColor($box, c);
		$box.next().html(c);
	},

	_setBoxColor: function($box, c) {
		if(c===transColor){
			$box.addClass('evo-transparent')
				.removeAttr('style');
		}else{
			$box.removeClass('evo-transparent')
				.attr('style','background-color:'+c);
		}
	},

	_setOption: function(key, value) {
		if(key=='color'){
			this._setValue(value, true);
		}else{
			this.options[key]=value;
		}
	},

	_add2History: function(c) {
		var iMax=history.length;
		// skip color if already in history
		for(var i=0;i<iMax;i++){
			if(c==history[i]){
				return;
			}
		}
		// limit of 28 colors in history
		if(iMax>27){
			history.shift();
		}
		// add to history
		history.push(c);
	},

	enable: function() {
		var e=this.element;
		if(this._isPopup){
			e.removeAttr('disabled');
		}else{
			e.css({
				'opacity': '1', 
				'pointer-events': 'auto'
			});
		}
		if(this.options.showOn!=='focus'){
			this.element.next().addClass('evo-pointer');
		}
		e.removeAttr('aria-disabled');
		this._enabled=true;
		return this;
	},

	disable: function() {
		var e=this.element;
		if(this._isPopup){
			e.attr('disabled', 'disabled');
		}else{
			this.hidePalette();
			e.css({
				'opacity': '0.3', 
				'pointer-events': 'none'
			});
		}
		if(this.options.showOn!=='focus'){
			this.element.next().removeClass('evo-pointer');
		}
		e.attr('aria-disabled','true');
		this._enabled=false;
		return this;
	},

	isDisabled: function() {
		return !this._enabled;
	},

	destroy: function() {
		$(document.body).off('click.'+this._id);
		if(this._palette){
			this._palette.off('mouseover click', 'td,.evo-cHist>div,.evo-transparent')
				.find('.evo-more a').off('click');
			if(this._isPopup){
				this._palette.remove();
			}
			this._palette=this._cTxt=null;
		}
		if(this._isPopup){
			this.element
				.next().off('click').remove()
				.end().off('focus').unwrap();
		}
		this.element.removeClass('colorPicker '+this.id).empty();
		$.Widget.prototype.destroy.call(this);
	}

});

})(jQuery);

/*! 
* Date range picker - v2.3.17
* http://www.filamentgroup.com/examples/daterangepicker/
* Copyright (c) 2010 - Filament Group (Scott Jehl); Licensed MIT, GPL licenses */

(function ($) {

/**
* --------------------------------------------------------------------
* jQuery-Plugin "daterangepicker.jQuery.js"
* by Scott Jehl, scott@filamentgroup.com
* reference article: http://www.filamentgroup.com/lab/update_date_range_picker_with_jquery_ui/
* demo page: http://www.filamentgroup.com/examples/daterangepicker/
*
* Copyright (c) 2010 Filament Group, Inc
* Dual licensed under the MIT (filamentgroup.com/examples/mit-license.txt) and GPL (filamentgroup.com/examples/gpl-license.txt) licenses.
*
* Dependencies: jquery, jquery UI datepicker, date.js, jQuery UI CSS Framework

* 12.15.2010 Made some fixes to resolve breaking changes introduced by jQuery UI 1.8.7
* --------------------------------------------------------------------
*/
$.fn.daterangepicker = function(settings){
var rangeInput = $(this);

//defaults
var options = $.extend({
presetRanges: [
{text: 'Today', dateStart: 'today', dateEnd: 'today' },
{text: 'Last 7 days', dateStart: 'today-7days', dateEnd: 'today' },
{text: 'Month to date', dateStart: function(){ return Date.parse('today').moveToFirstDayOfMonth(); }, dateEnd: 'today' },
{text: 'Year to date', dateStart: function(){ var x= Date.parse('today'); x.setMonth(0); x.setDate(1); return x; }, dateEnd: 'today' },
//extras:
{text: 'The previous Month', dateStart: function(){ return Date.parse('1 month ago').moveToFirstDayOfMonth(); }, dateEnd: function(){ return Date.parse('1 month ago').moveToLastDayOfMonth(); } }
//{text: 'Tomorrow', dateStart: 'Tomorrow', dateEnd: 'Tomorrow' },
//{text: 'Ad Campaign', dateStart: '03/07/08', dateEnd: 'Today' },
//{text: 'Last 30 Days', dateStart: 'Today-30', dateEnd: 'Today' },
//{text: 'Next 30 Days', dateStart: 'Today', dateEnd: 'Today+30' },
//{text: 'Our Ad Campaign', dateStart: '03/07/08', dateEnd: '07/08/08' }
],
//presetRanges: array of objects for each menu preset.
//Each obj must have text, dateStart, dateEnd. dateStart, dateEnd accept date.js string or a function which returns a date object
presets: {
specificDate: 'Specific Date',
allDatesBefore: 'All Dates Before',
allDatesAfter: 'All Dates After',
dateRange: 'Date Range'
},
rangeStartTitle: 'Start date',
rangeEndTitle: 'End date',
nextLinkText: 'Next',
prevLinkText: 'Prev',
target: rangeInput,
doneButtonText: 'Done',
earliestDate: Date.parse('-15years'), //earliest date allowed
latestDate: Date.parse('+15years'), //latest date allowed
constrainDates: false,
rangeSplitter: '-', //string to use between dates in single input
dateFormat: 'm/d/yy', // date formatting. Available formats: http://docs.jquery.com/UI/Datepicker/%24.datepicker.formatDate
closeOnSelect: true, //if a complete selection is made, close the menu
arrows: false,
appendTo: 'body',
onClose: function(){},
onOpen: function(){},
onChange: function(){},
datepickerOptions: null //object containing native UI datepicker API options
}, settings);



//custom datepicker options, extended by options
var datepickerOptions = {
onSelect: function(dateText, inst) {
var range_start = rp.find('.range-start');
var range_end = rp.find('.range-end');

if(rp.find('.ui-daterangepicker-specificDate').is('.ui-state-active')){
range_end.datepicker('setDate', range_start.datepicker('getDate') );
}

$(this).trigger('constrainOtherPicker');

var rangeA = fDate( range_start.datepicker('getDate') );
var rangeB = fDate( range_end.datepicker('getDate') );

//send back to input or inputs
if(rangeInput.length == 2){
rangeInput.eq(0).val(rangeA);
rangeInput.eq(1).val(rangeB);
}
else{
rangeInput.val((rangeA != rangeB) ? rangeA+' '+ options.rangeSplitter +' '+rangeB : rangeA);
}
//if closeOnSelect is true
if(options.closeOnSelect){
if(!rp.find('li.ui-state-active').is('.ui-daterangepicker-dateRange') && !rp.is(':animated') ){
hideRP();
}

$(this).trigger('constrainOtherPicker');

options.onChange();
}
},
defaultDate: +0
};

//change event fires both when a calendar is updated or a change event on the input is triggered
rangeInput.bind('change', options.onChange);

//datepicker options from options
options.datepickerOptions = (settings) ? $.extend(datepickerOptions, settings.datepickerOptions) : datepickerOptions;

//Capture Dates from input(s)
var inputDateA, inputDateB = Date.parse('today');
var inputDateAtemp, inputDateBtemp;
if(rangeInput.size() == 2){
inputDateAtemp = Date.parse( rangeInput.eq(0).val() );
inputDateBtemp = Date.parse( rangeInput.eq(1).val() );
if(inputDateAtemp == null){inputDateAtemp = inputDateBtemp;}
if(inputDateBtemp == null){inputDateBtemp = inputDateAtemp;}
}
else {
inputDateAtemp = Date.parse( rangeInput.val().split(options.rangeSplitter)[0] );
inputDateBtemp = Date.parse( rangeInput.val().split(options.rangeSplitter)[1] );
if(inputDateBtemp == null){inputDateBtemp = inputDateAtemp;} //if one date, set both
}
if(inputDateAtemp != null){inputDateA = inputDateAtemp;}
if(inputDateBtemp != null){inputDateB = inputDateBtemp;}


//build picker and
var rp = $('<div class="ui-daterangepicker ui-widget ui-helper-clearfix ui-widget-content ui-corner-all"></div>');
var rpPresets = (function(){
var ul = $('<ul class="ui-widget-content"></ul>').appendTo(rp);
$.each(options.presetRanges,function(){
$('<li class="ui-daterangepicker-'+ this.text.replace(/ /g, '') +' ui-corner-all"><a href="#">'+ this.text +'</a></li>')
.data('dateStart', this.dateStart)
.data('dateEnd', this.dateEnd)
.appendTo(ul);
});
var x=0;
$.each(options.presets, function(key, value) {
$('<li class="ui-daterangepicker-'+ key +' preset_'+ x +' ui-helper-clearfix ui-corner-all"><span class="ui-icon ui-icon-triangle-1-e"></span><a href="#">'+ value +'</a></li>')
.appendTo(ul);
x++;
});

ul.find('li').hover(
function(){
$(this).addClass('ui-state-hover');
},
function(){
$(this).removeClass('ui-state-hover');
})
.click(function(){
rp.find('.ui-state-active').removeClass('ui-state-active');
$(this).addClass('ui-state-active');
clickActions($(this),rp, rpPickers, doneBtn);
return false;
});
return ul;
})();

//function to format a date string
function fDate(date){
if(date == null || !date.getDate()){return '';}
var day = date.getDate();
var month = date.getMonth();
var year = date.getFullYear();
month++; // adjust javascript month
var dateFormat = options.dateFormat;
return $.datepicker.formatDate( dateFormat, date );
}


$.fn.restoreDateFromData = function(){
if($(this).data('saveDate')){
$(this).datepicker('setDate', $(this).data('saveDate')).removeData('saveDate');
}
return this;
};
$.fn.saveDateToData = function(){
if(!$(this).data('saveDate')){
$(this).data('saveDate', $(this).datepicker('getDate') );
}
return this;
};

//show, hide, or toggle rangepicker
function showRP(){
if(rp.data('state') == 'closed'){
positionRP();
rp.fadeIn(300).data('state', 'open');
options.onOpen();
}
}
function hideRP(){
if(rp.data('state') == 'open'){
rp.fadeOut(300).data('state', 'closed');
options.onClose();
}
}
function toggleRP(){
if( rp.data('state') == 'open' ){ hideRP(); }
else { showRP(); }
}
function positionRP(){
var relEl = riContain || rangeInput; //if arrows, use parent for offsets
var riOffset = relEl.offset(),
side = 'left',
val = riOffset.left,
offRight = $(window).width() - val - relEl.outerWidth();

if(val > offRight){
side = 'right', val = offRight;
}

rp.parent().css(side, val).css('top', riOffset.top + relEl.outerHeight());
}



//preset menu click events
function clickActions(el, rp, rpPickers, doneBtn){

if(el.is('.ui-daterangepicker-specificDate')){
//Specific Date (show the "start" calendar)
doneBtn.hide();
rpPickers.show();
rp.find('.title-start').text( options.presets.specificDate );
rp.find('.range-start').restoreDateFromData().css('opacity',1).show(400);
rp.find('.range-end').restoreDateFromData().css('opacity',0).hide(400);
setTimeout(function(){doneBtn.fadeIn();}, 400);
}
else if(el.is('.ui-daterangepicker-allDatesBefore')){
//All dates before specific date (show the "end" calendar and set the "start" calendar to the earliest date)
doneBtn.hide();
rpPickers.show();
rp.find('.title-end').text( options.presets.allDatesBefore );
rp.find('.range-start').saveDateToData().datepicker('setDate', options.earliestDate).css('opacity',0).hide(400);
rp.find('.range-end').restoreDateFromData().css('opacity',1).show(400);
setTimeout(function(){doneBtn.fadeIn();}, 400);
}
else if(el.is('.ui-daterangepicker-allDatesAfter')){
//All dates after specific date (show the "start" calendar and set the "end" calendar to the latest date)
doneBtn.hide();
rpPickers.show();
rp.find('.title-start').text( options.presets.allDatesAfter );
rp.find('.range-start').restoreDateFromData().css('opacity',1).show(400);
rp.find('.range-end').saveDateToData().datepicker('setDate', options.latestDate).css('opacity',0).hide(400);
setTimeout(function(){doneBtn.fadeIn();}, 400);
}
else if(el.is('.ui-daterangepicker-dateRange')){
//Specific Date range (show both calendars)
doneBtn.hide();
rpPickers.show();
rp.find('.title-start').text(options.rangeStartTitle);
rp.find('.title-end').text(options.rangeEndTitle);
rp.find('.range-start').restoreDateFromData().css('opacity',1).show(400);
rp.find('.range-end').restoreDateFromData().css('opacity',1).show(400);
setTimeout(function(){doneBtn.fadeIn();}, 400);
}
else {
//custom date range specified in the options (no calendars shown)
doneBtn.hide();
rp.find('.range-start, .range-end').css('opacity',0).hide(400, function(){
rpPickers.hide();
});
var dateStart = (typeof el.data('dateStart') == 'string') ? Date.parse(el.data('dateStart')) : el.data('dateStart')();
var dateEnd = (typeof el.data('dateEnd') == 'string') ? Date.parse(el.data('dateEnd')) : el.data('dateEnd')();
rp.find('.range-start').datepicker('setDate', dateStart).find('.ui-datepicker-current-day').trigger('click');
rp.find('.range-end').datepicker('setDate', dateEnd).find('.ui-datepicker-current-day').trigger('click');
}

return false;
}


//picker divs
var rpPickers = $('<div class="ranges ui-widget-header ui-corner-all ui-helper-clearfix"><div class="range-start"><span class="title-start">Start Date</span></div><div class="range-end"><span class="title-end">End Date</span></div></div>').appendTo(rp);
rpPickers.find('.range-start, .range-end')
.datepicker(options.datepickerOptions);


rpPickers.find('.range-start').datepicker('setDate', inputDateA);
rpPickers.find('.range-end').datepicker('setDate', inputDateB);

rpPickers.find('.range-start, .range-end')
.bind('constrainOtherPicker', function(){
if(options.constrainDates){
//constrain dates
if($(this).is('.range-start')){
rp.find('.range-end').datepicker( "option", "minDate", $(this).datepicker('getDate'));
}
else{
rp.find('.range-start').datepicker( "option", "maxDate", $(this).datepicker('getDate'));
}
}
})
.trigger('constrainOtherPicker');

var doneBtn = $('<button class="btnDone ui-state-default ui-corner-all">'+ options.doneButtonText +'</button>')
.click(function(){
rp.find('.ui-datepicker-current-day').trigger('click');
hideRP();
})
.hover(
function(){
$(this).addClass('ui-state-hover');
},
function(){
$(this).removeClass('ui-state-hover');
}
)
.appendTo(rpPickers);




//inputs toggle rangepicker visibility
$(this).click(function(){
toggleRP();
return false;
});
//hide em all
rpPickers.hide().find('.range-start, .range-end, .btnDone').hide();

rp.data('state', 'closed');

//Fixed for jQuery UI 1.8.7 - Calendars are hidden otherwise!
rpPickers.find('.ui-datepicker').css("display","block");

//inject rp
$(options.appendTo).append(rp);

//wrap and position
rp.wrap('<div class="ui-daterangepickercontain"></div>');

//add arrows (only available on one input)
if(options.arrows && rangeInput.size()==1){
var prevLink = $('<a href="#" class="ui-daterangepicker-prev ui-corner-all" title="'+ options.prevLinkText +'"><span class="ui-icon ui-icon-circle-triangle-w">'+ options.prevLinkText +'</span></a>');
var nextLink = $('<a href="#" class="ui-daterangepicker-next ui-corner-all" title="'+ options.nextLinkText +'"><span class="ui-icon ui-icon-circle-triangle-e">'+ options.nextLinkText +'</span></a>');

$(this)
.addClass('ui-rangepicker-input ui-widget-content')
.wrap('<div class="ui-daterangepicker-arrows ui-widget ui-widget-header ui-helper-clearfix ui-corner-all"></div>')
.before( prevLink )
.before( nextLink )
.parent().find('a').click(function(){
var dateA = rpPickers.find('.range-start').datepicker('getDate');
var dateB = rpPickers.find('.range-end').datepicker('getDate');
var diff = Math.abs( new TimeSpan(dateA - dateB).getTotalMilliseconds() ) + 86400000; //difference plus one day
if($(this).is('.ui-daterangepicker-prev')){ diff = -diff; }

rpPickers.find('.range-start, .range-end ').each(function(){
var thisDate = $(this).datepicker( "getDate");
if(thisDate == null){return false;}
$(this).datepicker( "setDate", thisDate.add({milliseconds: diff}) ).find('.ui-datepicker-current-day').trigger('click');
});
return false;
})
.hover(
function(){
$(this).addClass('ui-state-hover');
},
function(){
$(this).removeClass('ui-state-hover');
});

var riContain = rangeInput.parent();
}


$(document).click(function(){
if (rp.is(':visible')) {
hideRP();
}
});

rp.click(function(){return false;}).hide();
return this;
}

})(jQuery);

(function($, undefined){

$.widget("ui.panel", {
	options: {
		height: 200,
		width: 400,
		linkWidth: 100,
		click: false,
		flip: false
	},

	_create: function(){
		var self = this,
			options = self.options,
			el = self.element;
		el.addClass("ui-panel ui-widget ui-helper-reset ui-corner-all");
		self._oWidth = el.css("width");
		var p = el.children("div").addClass("ui-panel-content");
		self._pWidth = p.css("width");
		self._pHeight = p.css("height");
		if(options.flip){
			p.addClass('ui-panel-content-flip');
		}
		var ul = el.children("ul").addClass("ui-panel-list ui-widget-header");
		self._lHeight = ul.css("height");
		self._lWidth = ul.css("width");
		if(options.flip){
			ul.addClass('ui-panel-list-flip');
		}
		if(self._multi = (p.length > 1) ? true : false){
			ul.children("li").first().addClass("ui-state-selected ui-state-active");
			p.hide().first().show();
		}
		ul.children("li").addClass("ui-state-default ui-panel-item")
			.mouseover(function(event){self._mouseover(event); return false; })
			.mouseout(function(event){self._mouseout(event); return false; })
			.click(function(event){self._click(event); return false; });
		self.resize();
		$(window).bind("resize", function(event){ self.resize(); });
	},
	
	_mouseover: function(event){
		var self = this,
			options = self.options;
		if(options.mouseover){
			if(false == options.mouseover(event)){
				return false;
			}
		}
		if(options.hover){
			if(false == options.hover(event)){
				return false;
			}
		}
		$(event.target).addClass("ui-state-hover");
	},
	
	_mouseout: function(event){
		var self = this,
			options = self.options;
		if(options.mouseout){
			if(false == options.mouseout(event)){
				return false;
			}
		}
		if(options.hover){
			if(false == options.hover(event)){
				return false;
			}
		}
		$(event.target).removeClass("ui-state-hover");
	},
	
	_click: function(event){
		var self = this,
			options = self.options,
			me = $(event.target);
		if(options.beforeClick){
			if(false === options.beforeClick(event)){
				return false;
			}
		} 
		if(options.click){
			if(false === options.click(event)){
				return false;
			}
		} else if(self._multi){
			var a = self.element.children("div"),
				b = a.slice(me.index(), me.index()+1);
			if(b.length > 0){
				a.hide();
				b.show();
			}
		}
		self.element.children("ul").children("li.ui-state-selected").removeClass("ui-state-selected ui-state-active");
		me.addClass("ui-state-selected ui-state-active");
		if(options.afterClick){
			if(false === options.afterClick(event)){
				return false;
			}
		} 
	},
	
	_setBeforeClick: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.beforeClick;
		}
		options.beforeClick = value;
		return self;
	},
	
	_setClick: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.click;
		}
		options.click = value;
		return self;
	},
	
	_setAfterClick: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.afterClick;
		}
		options.afterClick = value;
		return self;
	},
	
	_height: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.height;
		}
		options.height = value;
		return self.resize();
	},
	
	_width: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.width;
		}
		options.width = value;
		return self.resize();
	},
	
	_linkWidth: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.linkWidth;
		}
		options.linkWidth = value;
		return self.resize();
	},
	
	_flip: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.flip;
		}
		options.flip = value;
		return self.flip();
	},
	
	add: function(link, content){
		var self = this,
			el = self.element;
		if(link){
			var d = (typeof link === "string") ? $('<li>' + link + '</li>') : $(link);
			d.addClass("ui-state-default ui-panel-item")
				.mouseover(function(event){self._mouseover(event); return false; })
				.mouseout(function(event){self._mouseout(event); return false; })
				.click(function(event){self._click(event); return false; });
			el.children("ul").append(d);
		}
		if(content){
			var e = (typeof content === "string") ? $('<div>' + content + '</div>') : $(content);
			e.addClass("ui-panel-content");
			el.append(e);
		}
		return self;
	},
	
	remove: function(context, bcontext){
		var self = this,
			el = self.element;
		el.children("ul").find(context || "li.ui-state-active").remove();
		el.children("div").filter(bcontext || ":visible").remove();
		return self;
	},
	
	contents: function(content, context){
		var self = this,
			el = self.element,
			context = context ? $(context) : el.children("div").filter(":visible");
		context.html(content);
		return self;
	},
	
	append: function(content, context){
		var self = this,
			el = self.element,
			context = context ? $(context) : el.children("div").filter(":visible");
		context.append(content);
		return self;
	},
	
	prepend: function(content, context){
		var self = this,
			el = self.element,
			context = context ? $(context) : el.children("div").filter(":visible");
		context.prepend(content);
		return self;
	},
	
	resize: function(width, height, lWidth){
		var self = this,
			options = self.options,
			el = self.element,
			width = options.width = (width || options.width),
			height = options.height = (height || options.height),
			lWidth = options.linkWidth = (lWidth || options.linkWidth);
		el.width(width);
		var ul = el.children("ul");
		var p = el.children("div").height(height);
		ul.height((height > p.outerHeight()) ? height : p.outerHeight()).width((("" + lWidth).search("%") > -1) ? (el.width() * ('.' + parseFloat(lWidth))) : ((lWidth + 100) > width) ? (width - 100) : lWidth);
		p.width((el.width() - ul.outerWidth()) - (p.outerWidth() - p.width()) - 2);
		return self;
	},
	
	flip: function(flip){
		var self = this,
			options = self.options,
			el = self.element,
			flip = options.flip = (flip === undefined ? !options.flip : flip);
		if(flip){
			el.children("ul").addClass("ui-panel-list-flip");
			el.children("div").addClass("ui-panel-content-flip");
		} else {
			el.children("ul").removeClass("ui-panel-list-flip");
			el.children("div").removeClass("ui-panel-content-flip");
		}
		alert(options.flip);
		return self;
	},

	destroy: function() {
		var self = this,
			el = self.element;

		el.removeClass("ui-panel ui-widget ui-helper-reset ui-corner-all").css("width", self._oWidth);
		el.children("ul").removeClass("ui-panel-list ui-widget-header ui-panel-list-flip").css("height", self._lHeight).css("width", self._lWidth).children("li").removeClass("ui-state-default ui-state-selected ui-panel-item").unbind();
		el.children("div").removeClass("ui-panel-content ui-content-panel-flip").css("width", self._pWidth).css("height", self._pHeight);
		return $.Widget.prototype.destroy.call(this);
	},
	
	_setOption: function(key, value){
		var self = this;

		switch (key) {
			case "height":
				self._height(value);
				break;
			case "width":
				self._width(value);
				break;
			case "linkWidth":
				self._linkWidth(value);
				break;
			case "beforeClick":
				self._setBeforeClick(value);
				break;
			case "click":
				self._setClick(value);
				break;
			case "afterClick":
				self._setAfterClick(value);
				break;
			case "flip":
				self._flip(value);
				break;
		}

		$.Widget.prototype._setOption.apply(self, arguments);
	}
});
$.extend($.ui.panel, {
	version: "0.5"
});
})(jQuery);
/*! 
* jQuery timepicker plugin - v1.0.1
* http://trentrichardson.com
* Copyright (c) 2012 Trent Richardson; Licensed MIT, GPL */

/*
* jQuery timepicker addon
* By: Trent Richardson [http://trentrichardson.com]
* Version 1.0.1
* Last Modified: 07/01/2012
*
* Copyright 2012 Trent Richardson
* You may use this project under MIT or GPL licenses.
* http://trentrichardson.com/Impromptu/GPL-LICENSE.txt
* http://trentrichardson.com/Impromptu/MIT-LICENSE.txt
*
* HERES THE CSS:
* .ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
* .ui-timepicker-div dl { text-align: left; }
* .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
* .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
* .ui-timepicker-div td { font-size: 90%; }
* .ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
*/

/*jslint evil: true, maxlen: 300, white: false, undef: false, nomen: false, onevar: false */

(function($) {

// Prevent "Uncaught RangeError: Maximum call stack size exceeded"
$.ui.timepicker = $.ui.timepicker || {};
if ($.ui.timepicker.version) {
	return;
}

$.extend($.ui, { timepicker: { version: "1.0.1" } });

/* Time picker manager.
   Use the singleton instance of this class, $.timepicker, to interact with the time picker.
   Settings for (groups of) time pickers are maintained in an instance object,
   allowing multiple different settings on the same page. */

function Timepicker() {
	this.regional = []; // Available regional settings, indexed by language code
	this.regional[''] = { // Default regional settings
		currentText: 'Now',
		closeText: 'Done',
		ampm: false,
		amNames: ['AM', 'A'],
		pmNames: ['PM', 'P'],
		timeFormat: 'hh:mm tt',
		timeSuffix: '',
		timeOnlyTitle: 'Choose Time',
		timeText: 'Time',
		hourText: 'Hour',
		minuteText: 'Minute',
		secondText: 'Second',
		millisecText: 'Millisecond',
		timezoneText: 'Time Zone'
	};
	this._defaults = { // Global defaults for all the datetime picker instances
		showButtonPanel: true,
		timeOnly: false,
		showHour: true,
		showMinute: true,
		showSecond: false,
		showMillisec: false,
		showTimezone: false,
		showTime: true,
		stepHour: 1,
		stepMinute: 1,
		stepSecond: 1,
		stepMillisec: 1,
		hour: 0,
		minute: 0,
		second: 0,
		millisec: 0,
		timezone: null,
		useLocalTimezone: false,
		defaultTimezone: "+0000",
		hourMin: 0,
		minuteMin: 0,
		secondMin: 0,
		millisecMin: 0,
		hourMax: 23,
		minuteMax: 59,
		secondMax: 59,
		millisecMax: 999,
		minDateTime: null,
		maxDateTime: null,
		onSelect: null,
		hourGrid: 0,
		minuteGrid: 0,
		secondGrid: 0,
		millisecGrid: 0,
		alwaysSetTime: true,
		separator: ' ',
		altFieldTimeOnly: true,
		showTimepicker: true,
		timezoneIso8601: false,
		timezoneList: null,
		addSliderAccess: false,
		sliderAccessArgs: null
	};
	$.extend(this._defaults, this.regional['']);
}

$.extend(Timepicker.prototype, {
	$input: null,
	$altInput: null,
	$timeObj: null,
	inst: null,
	hour_slider: null,
	minute_slider: null,
	second_slider: null,
	millisec_slider: null,
	timezone_select: null,
	hour: 0,
	minute: 0,
	second: 0,
	millisec: 0,
	timezone: null,
	defaultTimezone: "+0000",
	hourMinOriginal: null,
	minuteMinOriginal: null,
	secondMinOriginal: null,
	millisecMinOriginal: null,
	hourMaxOriginal: null,
	minuteMaxOriginal: null,
	secondMaxOriginal: null,
	millisecMaxOriginal: null,
	ampm: '',
	formattedDate: '',
	formattedTime: '',
	formattedDateTime: '',
	timezoneList: null,

	/* Override the default settings for all instances of the time picker.
	   @param  settings  object - the new settings to use as defaults (anonymous object)
	   @return the manager object */
	setDefaults: function(settings) {
		extendRemove(this._defaults, settings || {});
		return this;
	},

	//########################################################################
	// Create a new Timepicker instance
	//########################################################################
	_newInst: function($input, o) {
		var tp_inst = new Timepicker(),
			inlineSettings = {};

		for (var attrName in this._defaults) {
			var attrValue = $input.attr('time:' + attrName);
			if (attrValue) {
				try {
					inlineSettings[attrName] = eval(attrValue);
				} catch (err) {
					inlineSettings[attrName] = attrValue;
				}
			}
		}
		tp_inst._defaults = $.extend({}, this._defaults, inlineSettings, o, {
			beforeShow: function(input, dp_inst) {
				if ($.isFunction(o.beforeShow)) {
					return o.beforeShow(input, dp_inst, tp_inst);
                }
			},
			onChangeMonthYear: function(year, month, dp_inst) {
				// Update the time as well : this prevents the time from disappearing from the $input field.
				tp_inst._updateDateTime(dp_inst);
				if ($.isFunction(o.onChangeMonthYear)) {
					o.onChangeMonthYear.call($input[0], year, month, dp_inst, tp_inst);
                }
			},
			onClose: function(dateText, dp_inst) {
				if (tp_inst.timeDefined === true && $input.val() !== '') {
					tp_inst._updateDateTime(dp_inst);
                }
				if ($.isFunction(o.onClose)) {
					o.onClose.call($input[0], dateText, dp_inst, tp_inst);
                }
			},
			timepicker: tp_inst // add timepicker as a property of datepicker: $.datepicker._get(dp_inst, 'timepicker');
		});
		tp_inst.amNames = $.map(tp_inst._defaults.amNames, function(val) { return val.toUpperCase(); });
		tp_inst.pmNames = $.map(tp_inst._defaults.pmNames, function(val) { return val.toUpperCase(); });

		if (tp_inst._defaults.timezoneList === null) {
			var timezoneList = [];
			for (var i = -11; i <= 12; i++) {
				timezoneList.push((i >= 0 ? '+' : '-') + ('0' + Math.abs(i).toString()).slice(-2) + '00');
            }
			if (tp_inst._defaults.timezoneIso8601) {
				timezoneList = $.map(timezoneList, function(val) {
					return val == '+0000' ? 'Z' : (val.substring(0, 3) + ':' + val.substring(3));
				});
            }
			tp_inst._defaults.timezoneList = timezoneList;
		}

		tp_inst.timezone = tp_inst._defaults.timezone;
		tp_inst.hour = tp_inst._defaults.hour;
		tp_inst.minute = tp_inst._defaults.minute;
		tp_inst.second = tp_inst._defaults.second;
		tp_inst.millisec = tp_inst._defaults.millisec;
		tp_inst.ampm = '';
		tp_inst.$input = $input;

		if (o.altField) {
			tp_inst.$altInput = $(o.altField)
				.css({ cursor: 'pointer' })
				.focus(function(){ $input.trigger("focus"); });
        }

		if(tp_inst._defaults.minDate===0 || tp_inst._defaults.minDateTime===0)
		{
			tp_inst._defaults.minDate=new Date();
		}
		if(tp_inst._defaults.maxDate===0 || tp_inst._defaults.maxDateTime===0)
		{
			tp_inst._defaults.maxDate=new Date();
		}

		// datepicker needs minDate/maxDate, timepicker needs minDateTime/maxDateTime..
		if(tp_inst._defaults.minDate !== undefined && tp_inst._defaults.minDate instanceof Date) {
			tp_inst._defaults.minDateTime = new Date(tp_inst._defaults.minDate.getTime());
        }
		if(tp_inst._defaults.minDateTime !== undefined && tp_inst._defaults.minDateTime instanceof Date) {
			tp_inst._defaults.minDate = new Date(tp_inst._defaults.minDateTime.getTime());
        }
		if(tp_inst._defaults.maxDate !== undefined && tp_inst._defaults.maxDate instanceof Date) {
			tp_inst._defaults.maxDateTime = new Date(tp_inst._defaults.maxDate.getTime());
        }
		if(tp_inst._defaults.maxDateTime !== undefined && tp_inst._defaults.maxDateTime instanceof Date) {
			tp_inst._defaults.maxDate = new Date(tp_inst._defaults.maxDateTime.getTime());
        }
		return tp_inst;
	},

	//########################################################################
	// add our sliders to the calendar
	//########################################################################
	_addTimePicker: function(dp_inst) {
		var currDT = (this.$altInput && this._defaults.altFieldTimeOnly) ?
				this.$input.val() + ' ' + this.$altInput.val() :
				this.$input.val();

		this.timeDefined = this._parseTime(currDT);
		this._limitMinMaxDateTime(dp_inst, false);
		this._injectTimePicker();
	},

	//########################################################################
	// parse the time string from input value or _setTime
	//########################################################################
	_parseTime: function(timeString, withDate) {
		if (!this.inst) {
			this.inst = $.datepicker._getInst(this.$input[0]);
		}
		
		if (withDate || !this._defaults.timeOnly) 
		{
			var dp_dateFormat = $.datepicker._get(this.inst, 'dateFormat');
			try {
				var parseRes = parseDateTimeInternal(dp_dateFormat, this._defaults.timeFormat, timeString, $.datepicker._getFormatConfig(this.inst), this._defaults);
				if (!parseRes.timeObj) { return false; }
				$.extend(this, parseRes.timeObj);
			} catch (err)
			{
				return false;
			}
			return true;
		}
		else
		{
			var timeObj = $.datepicker.parseTime(this._defaults.timeFormat, timeString, this._defaults);
			if(!timeObj) { return false; }
			$.extend(this, timeObj);
			return true;
		}
	},
	
	//########################################################################
	// generate and inject html for timepicker into ui datepicker
	//########################################################################
	_injectTimePicker: function() {
		var $dp = this.inst.dpDiv,
			o = this._defaults,
			tp_inst = this,
			// Added by Peter Medeiros:
			// - Figure out what the hour/minute/second max should be based on the step values.
			// - Example: if stepMinute is 15, then minMax is 45.
			hourMax = parseInt((o.hourMax - ((o.hourMax - o.hourMin) % o.stepHour)) ,10),
			minMax  = parseInt((o.minuteMax - ((o.minuteMax - o.minuteMin) % o.stepMinute)) ,10),
			secMax  = parseInt((o.secondMax - ((o.secondMax - o.secondMin) % o.stepSecond)) ,10),
			millisecMax  = parseInt((o.millisecMax - ((o.millisecMax - o.millisecMin) % o.stepMillisec)) ,10),
			dp_id = this.inst.id.toString().replace(/([^A-Za-z0-9_])/g, '');

		// Prevent displaying twice
		//if ($dp.find("div#ui-timepicker-div-"+ dp_id).length === 0) {
		if ($dp.find("div#ui-timepicker-div-"+ dp_id).length === 0 && o.showTimepicker) {
			var noDisplay = ' style="display:none;"',
				html =	'<div class="ui-timepicker-div" id="ui-timepicker-div-' + dp_id + '"><dl>' +
						'<dt class="ui_tpicker_time_label" id="ui_tpicker_time_label_' + dp_id + '"' +
						((o.showTime) ? '' : noDisplay) + '>' + o.timeText + '</dt>' +
						'<dd class="ui_tpicker_time" id="ui_tpicker_time_' + dp_id + '"' +
						((o.showTime) ? '' : noDisplay) + '></dd>' +
						'<dt class="ui_tpicker_hour_label" id="ui_tpicker_hour_label_' + dp_id + '"' +
						((o.showHour) ? '' : noDisplay) + '>' + o.hourText + '</dt>',
				hourGridSize = 0,
				minuteGridSize = 0,
				secondGridSize = 0,
				millisecGridSize = 0,
				size = null;

            // Hours
			html += '<dd class="ui_tpicker_hour"><div id="ui_tpicker_hour_' + dp_id + '"' +
						((o.showHour) ? '' : noDisplay) + '></div>';
			if (o.showHour && o.hourGrid > 0) {
				html += '<div style="padding-left: 1px"><table class="ui-tpicker-grid-label"><tr>';

				for (var h = o.hourMin; h <= hourMax; h += parseInt(o.hourGrid,10)) {
					hourGridSize++;
					var tmph = (o.ampm && h > 12) ? h-12 : h;
					if (tmph < 10) { tmph = '0' + tmph; }
					if (o.ampm) {
						if (h === 0) {
                            tmph = 12 +'a';
                        } else {
                            if (h < 12) { tmph += 'a'; }
						    else { tmph += 'p'; }
                        }
					}
					html += '<td>' + tmph + '</td>';
				}

				html += '</tr></table></div>';
			}
			html += '</dd>';

			// Minutes
			html += '<dt class="ui_tpicker_minute_label" id="ui_tpicker_minute_label_' + dp_id + '"' +
					((o.showMinute) ? '' : noDisplay) + '>' + o.minuteText + '</dt>'+
					'<dd class="ui_tpicker_minute"><div id="ui_tpicker_minute_' + dp_id + '"' +
							((o.showMinute) ? '' : noDisplay) + '></div>';

			if (o.showMinute && o.minuteGrid > 0) {
				html += '<div style="padding-left: 1px"><table class="ui-tpicker-grid-label"><tr>';

				for (var m = o.minuteMin; m <= minMax; m += parseInt(o.minuteGrid,10)) {
					minuteGridSize++;
					html += '<td>' + ((m < 10) ? '0' : '') + m + '</td>';
				}

				html += '</tr></table></div>';
			}
			html += '</dd>';

			// Seconds
			html += '<dt class="ui_tpicker_second_label" id="ui_tpicker_second_label_' + dp_id + '"' +
					((o.showSecond) ? '' : noDisplay) + '>' + o.secondText + '</dt>'+
					'<dd class="ui_tpicker_second"><div id="ui_tpicker_second_' + dp_id + '"'+
							((o.showSecond) ? '' : noDisplay) + '></div>';

			if (o.showSecond && o.secondGrid > 0) {
				html += '<div style="padding-left: 1px"><table><tr>';

				for (var s = o.secondMin; s <= secMax; s += parseInt(o.secondGrid,10)) {
					secondGridSize++;
					html += '<td>' + ((s < 10) ? '0' : '') + s + '</td>';
				}

				html += '</tr></table></div>';
			}
			html += '</dd>';

			// Milliseconds
			html += '<dt class="ui_tpicker_millisec_label" id="ui_tpicker_millisec_label_' + dp_id + '"' +
					((o.showMillisec) ? '' : noDisplay) + '>' + o.millisecText + '</dt>'+
					'<dd class="ui_tpicker_millisec"><div id="ui_tpicker_millisec_' + dp_id + '"'+
							((o.showMillisec) ? '' : noDisplay) + '></div>';

			if (o.showMillisec && o.millisecGrid > 0) {
				html += '<div style="padding-left: 1px"><table><tr>';

				for (var l = o.millisecMin; l <= millisecMax; l += parseInt(o.millisecGrid,10)) {
					millisecGridSize++;
					html += '<td>' + ((l < 10) ? '0' : '') + l + '</td>';
				}

				html += '</tr></table></div>';
			}
			html += '</dd>';

			// Timezone
			html += '<dt class="ui_tpicker_timezone_label" id="ui_tpicker_timezone_label_' + dp_id + '"' +
					((o.showTimezone) ? '' : noDisplay) + '>' + o.timezoneText + '</dt>';
			html += '<dd class="ui_tpicker_timezone" id="ui_tpicker_timezone_' + dp_id + '"'	+
							((o.showTimezone) ? '' : noDisplay) + '></dd>';

			html += '</dl></div>';
			var $tp = $(html);

				// if we only want time picker...
			if (o.timeOnly === true) {
				$tp.prepend(
					'<div class="ui-widget-header ui-helper-clearfix ui-corner-all">' +
						'<div class="ui-datepicker-title">' + o.timeOnlyTitle + '</div>' +
					'</div>');
				$dp.find('.ui-datepicker-header, .ui-datepicker-calendar').hide();
			}

			this.hour_slider = $tp.find('#ui_tpicker_hour_'+ dp_id).slider({
				orientation: "horizontal",
				value: this.hour,
				min: o.hourMin,
				max: hourMax,
				step: o.stepHour,
				slide: function(event, ui) {
					tp_inst.hour_slider.slider( "option", "value", ui.value);
					tp_inst._onTimeChange();
				}
			});


			// Updated by Peter Medeiros:
			// - Pass in Event and UI instance into slide function
			this.minute_slider = $tp.find('#ui_tpicker_minute_'+ dp_id).slider({
				orientation: "horizontal",
				value: this.minute,
				min: o.minuteMin,
				max: minMax,
				step: o.stepMinute,
				slide: function(event, ui) {
					tp_inst.minute_slider.slider( "option", "value", ui.value);
					tp_inst._onTimeChange();
				}
			});

			this.second_slider = $tp.find('#ui_tpicker_second_'+ dp_id).slider({
				orientation: "horizontal",
				value: this.second,
				min: o.secondMin,
				max: secMax,
				step: o.stepSecond,
				slide: function(event, ui) {
					tp_inst.second_slider.slider( "option", "value", ui.value);
					tp_inst._onTimeChange();
				}
			});

			this.millisec_slider = $tp.find('#ui_tpicker_millisec_'+ dp_id).slider({
				orientation: "horizontal",
				value: this.millisec,
				min: o.millisecMin,
				max: millisecMax,
				step: o.stepMillisec,
				slide: function(event, ui) {
					tp_inst.millisec_slider.slider( "option", "value", ui.value);
					tp_inst._onTimeChange();
				}
			});

			this.timezone_select = $tp.find('#ui_tpicker_timezone_'+ dp_id).append('<select></select>').find("select");
			$.fn.append.apply(this.timezone_select,
				$.map(o.timezoneList, function(val, idx) {
					return $("<option />")
						.val(typeof val == "object" ? val.value : val)
						.text(typeof val == "object" ? val.label : val);
				})
			);
			if (typeof(this.timezone) != "undefined" && this.timezone !== null && this.timezone !== "") {
				var local_date = new Date(this.inst.selectedYear, this.inst.selectedMonth, this.inst.selectedDay, 12);
				var local_timezone = timeZoneString(local_date);
				if (local_timezone == this.timezone) {
					selectLocalTimeZone(tp_inst);
				} else {
					this.timezone_select.val(this.timezone);
				}
			} else {
				if (typeof(this.hour) != "undefined" && this.hour !== null && this.hour !== "") {
					this.timezone_select.val(o.defaultTimezone);
				} else {
					selectLocalTimeZone(tp_inst);
				}
			}
			this.timezone_select.change(function() {
				tp_inst._defaults.useLocalTimezone = false;
				tp_inst._onTimeChange();
			});

			// Add grid functionality
			if (o.showHour && o.hourGrid > 0) {
				size = 100 * hourGridSize * o.hourGrid / (hourMax - o.hourMin);

				$tp.find(".ui_tpicker_hour table").css({
					width: size + "%",
					marginLeft: (size / (-2 * hourGridSize)) + "%",
					borderCollapse: 'collapse'
				}).find("td").each( function(index) {
					$(this).click(function() {
						var h = $(this).html();
						if(o.ampm)	{
							var ap = h.substring(2).toLowerCase(),
								aph = parseInt(h.substring(0,2), 10);
							if (ap == 'a') {
								if (aph == 12) { h = 0; }
								else { h = aph; }
							} else if (aph == 12) { h = 12; }
							else { h = aph + 12; }
						}
						tp_inst.hour_slider.slider("option", "value", h);
						tp_inst._onTimeChange();
						tp_inst._onSelectHandler();
					}).css({
						cursor: 'pointer',
						width: (100 / hourGridSize) + '%',
						textAlign: 'center',
						overflow: 'hidden'
					});
				});
			}

			if (o.showMinute && o.minuteGrid > 0) {
				size = 100 * minuteGridSize * o.minuteGrid / (minMax - o.minuteMin);
				$tp.find(".ui_tpicker_minute table").css({
					width: size + "%",
					marginLeft: (size / (-2 * minuteGridSize)) + "%",
					borderCollapse: 'collapse'
				}).find("td").each(function(index) {
					$(this).click(function() {
						tp_inst.minute_slider.slider("option", "value", $(this).html());
						tp_inst._onTimeChange();
						tp_inst._onSelectHandler();
					}).css({
						cursor: 'pointer',
						width: (100 / minuteGridSize) + '%',
						textAlign: 'center',
						overflow: 'hidden'
					});
				});
			}

			if (o.showSecond && o.secondGrid > 0) {
				$tp.find(".ui_tpicker_second table").css({
					width: size + "%",
					marginLeft: (size / (-2 * secondGridSize)) + "%",
					borderCollapse: 'collapse'
				}).find("td").each(function(index) {
					$(this).click(function() {
						tp_inst.second_slider.slider("option", "value", $(this).html());
						tp_inst._onTimeChange();
						tp_inst._onSelectHandler();
					}).css({
						cursor: 'pointer',
						width: (100 / secondGridSize) + '%',
						textAlign: 'center',
						overflow: 'hidden'
					});
				});
			}

			if (o.showMillisec && o.millisecGrid > 0) {
				$tp.find(".ui_tpicker_millisec table").css({
					width: size + "%",
					marginLeft: (size / (-2 * millisecGridSize)) + "%",
					borderCollapse: 'collapse'
				}).find("td").each(function(index) {
					$(this).click(function() {
						tp_inst.millisec_slider.slider("option", "value", $(this).html());
						tp_inst._onTimeChange();
						tp_inst._onSelectHandler();
					}).css({
						cursor: 'pointer',
						width: (100 / millisecGridSize) + '%',
						textAlign: 'center',
						overflow: 'hidden'
					});
				});
			}

			var $buttonPanel = $dp.find('.ui-datepicker-buttonpane');
			if ($buttonPanel.length) { $buttonPanel.before($tp); }
			else { $dp.append($tp); }

			this.$timeObj = $tp.find('#ui_tpicker_time_'+ dp_id);

			if (this.inst !== null) {
				var timeDefined = this.timeDefined;
				this._onTimeChange();
				this.timeDefined = timeDefined;
			}

			//Emulate datepicker onSelect behavior. Call on slidestop.
			var onSelectDelegate = function() {
				tp_inst._onSelectHandler();
			};
			this.hour_slider.bind('slidestop',onSelectDelegate);
			this.minute_slider.bind('slidestop',onSelectDelegate);
			this.second_slider.bind('slidestop',onSelectDelegate);
			this.millisec_slider.bind('slidestop',onSelectDelegate);

			// slideAccess integration: http://trentrichardson.com/2011/11/11/jquery-ui-sliders-and-touch-accessibility/
			if (this._defaults.addSliderAccess){
				var sliderAccessArgs = this._defaults.sliderAccessArgs;
				setTimeout(function(){ // fix for inline mode
					if($tp.find('.ui-slider-access').length === 0){
						$tp.find('.ui-slider:visible').sliderAccess(sliderAccessArgs);

						// fix any grids since sliders are shorter
						var sliderAccessWidth = $tp.find('.ui-slider-access:eq(0)').outerWidth(true);
						if(sliderAccessWidth){
							$tp.find('table:visible').each(function(){
								var $g = $(this),
									oldWidth = $g.outerWidth(),
									oldMarginLeft = $g.css('marginLeft').toString().replace('%',''),
									newWidth = oldWidth - sliderAccessWidth,
									newMarginLeft = ((oldMarginLeft * newWidth)/oldWidth) + '%';

								$g.css({ width: newWidth, marginLeft: newMarginLeft });
							});
						}
					}
				},0);
			}
			// end slideAccess integration

		}
	},

	//########################################################################
	// This function tries to limit the ability to go outside the
	// min/max date range
	//########################################################################
	_limitMinMaxDateTime: function(dp_inst, adjustSliders){
		var o = this._defaults,
			dp_date = new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay);

		if(!this._defaults.showTimepicker) { return; } // No time so nothing to check here

		if($.datepicker._get(dp_inst, 'minDateTime') !== null && $.datepicker._get(dp_inst, 'minDateTime') !== undefined && dp_date){
			var minDateTime = $.datepicker._get(dp_inst, 'minDateTime'),
				minDateTimeDate = new Date(minDateTime.getFullYear(), minDateTime.getMonth(), minDateTime.getDate(), 0, 0, 0, 0);

			if(this.hourMinOriginal === null || this.minuteMinOriginal === null || this.secondMinOriginal === null || this.millisecMinOriginal === null){
				this.hourMinOriginal = o.hourMin;
				this.minuteMinOriginal = o.minuteMin;
				this.secondMinOriginal = o.secondMin;
				this.millisecMinOriginal = o.millisecMin;
			}

			if(dp_inst.settings.timeOnly || minDateTimeDate.getTime() == dp_date.getTime()) {
				this._defaults.hourMin = minDateTime.getHours();
				if (this.hour <= this._defaults.hourMin) {
					this.hour = this._defaults.hourMin;
					this._defaults.minuteMin = minDateTime.getMinutes();
					if (this.minute <= this._defaults.minuteMin) {
						this.minute = this._defaults.minuteMin;
						this._defaults.secondMin = minDateTime.getSeconds();
					} else if (this.second <= this._defaults.secondMin){
						this.second = this._defaults.secondMin;
						this._defaults.millisecMin = minDateTime.getMilliseconds();
					} else {
						if(this.millisec < this._defaults.millisecMin) {
							this.millisec = this._defaults.millisecMin;
                        }
						this._defaults.millisecMin = this.millisecMinOriginal;
					}
				} else {
					this._defaults.minuteMin = this.minuteMinOriginal;
					this._defaults.secondMin = this.secondMinOriginal;
					this._defaults.millisecMin = this.millisecMinOriginal;
				}
			}else{
				this._defaults.hourMin = this.hourMinOriginal;
				this._defaults.minuteMin = this.minuteMinOriginal;
				this._defaults.secondMin = this.secondMinOriginal;
				this._defaults.millisecMin = this.millisecMinOriginal;
			}
		}

		if($.datepicker._get(dp_inst, 'maxDateTime') !== null && $.datepicker._get(dp_inst, 'maxDateTime') !== undefined && dp_date){
			var maxDateTime = $.datepicker._get(dp_inst, 'maxDateTime'),
				maxDateTimeDate = new Date(maxDateTime.getFullYear(), maxDateTime.getMonth(), maxDateTime.getDate(), 0, 0, 0, 0);

			if(this.hourMaxOriginal === null || this.minuteMaxOriginal === null || this.secondMaxOriginal === null){
				this.hourMaxOriginal = o.hourMax;
				this.minuteMaxOriginal = o.minuteMax;
				this.secondMaxOriginal = o.secondMax;
				this.millisecMaxOriginal = o.millisecMax;
			}

			if(dp_inst.settings.timeOnly || maxDateTimeDate.getTime() == dp_date.getTime()){
				this._defaults.hourMax = maxDateTime.getHours();
				if (this.hour >= this._defaults.hourMax) {
					this.hour = this._defaults.hourMax;
					this._defaults.minuteMax = maxDateTime.getMinutes();
					if (this.minute >= this._defaults.minuteMax) {
						this.minute = this._defaults.minuteMax;
						this._defaults.secondMax = maxDateTime.getSeconds();
					} else if (this.second >= this._defaults.secondMax) {
						this.second = this._defaults.secondMax;
						this._defaults.millisecMax = maxDateTime.getMilliseconds();
					} else {
						if(this.millisec > this._defaults.millisecMax) { this.millisec = this._defaults.millisecMax; }
						this._defaults.millisecMax = this.millisecMaxOriginal;
					}
				} else {
					this._defaults.minuteMax = this.minuteMaxOriginal;
					this._defaults.secondMax = this.secondMaxOriginal;
					this._defaults.millisecMax = this.millisecMaxOriginal;
				}
			}else{
				this._defaults.hourMax = this.hourMaxOriginal;
				this._defaults.minuteMax = this.minuteMaxOriginal;
				this._defaults.secondMax = this.secondMaxOriginal;
				this._defaults.millisecMax = this.millisecMaxOriginal;
			}
		}

		if(adjustSliders !== undefined && adjustSliders === true){
			var hourMax = parseInt((this._defaults.hourMax - ((this._defaults.hourMax - this._defaults.hourMin) % this._defaults.stepHour)) ,10),
                minMax  = parseInt((this._defaults.minuteMax - ((this._defaults.minuteMax - this._defaults.minuteMin) % this._defaults.stepMinute)) ,10),
                secMax  = parseInt((this._defaults.secondMax - ((this._defaults.secondMax - this._defaults.secondMin) % this._defaults.stepSecond)) ,10),
				millisecMax  = parseInt((this._defaults.millisecMax - ((this._defaults.millisecMax - this._defaults.millisecMin) % this._defaults.stepMillisec)) ,10);

			if(this.hour_slider) {
				this.hour_slider.slider("option", { min: this._defaults.hourMin, max: hourMax }).slider('value', this.hour);
            }
			if(this.minute_slider) {
				this.minute_slider.slider("option", { min: this._defaults.minuteMin, max: minMax }).slider('value', this.minute);
            }
			if(this.second_slider){
				this.second_slider.slider("option", { min: this._defaults.secondMin, max: secMax }).slider('value', this.second);
            }
			if(this.millisec_slider) {
				this.millisec_slider.slider("option", { min: this._defaults.millisecMin, max: millisecMax }).slider('value', this.millisec);
            }
		}

	},


	//########################################################################
	// when a slider moves, set the internal time...
	// on time change is also called when the time is updated in the text field
	//########################################################################
	_onTimeChange: function() {
		var hour   = (this.hour_slider) ? this.hour_slider.slider('value') : false,
			minute = (this.minute_slider) ? this.minute_slider.slider('value') : false,
			second = (this.second_slider) ? this.second_slider.slider('value') : false,
			millisec = (this.millisec_slider) ? this.millisec_slider.slider('value') : false,
			timezone = (this.timezone_select) ? this.timezone_select.val() : false,
			o = this._defaults;

		if (typeof(hour) == 'object') { hour = false; }
		if (typeof(minute) == 'object') { minute = false; }
		if (typeof(second) == 'object') { second = false; }
		if (typeof(millisec) == 'object') { millisec = false; }
		if (typeof(timezone) == 'object') { timezone = false; }

		if (hour !== false) { hour = parseInt(hour,10); }
		if (minute !== false) { minute = parseInt(minute,10); }
		if (second !== false) { second = parseInt(second,10); }
		if (millisec !== false) { millisec = parseInt(millisec,10); }

		var ampm = o[hour < 12 ? 'amNames' : 'pmNames'][0];

		// If the update was done in the input field, the input field should not be updated.
		// If the update was done using the sliders, update the input field.
		var hasChanged = (hour != this.hour || minute != this.minute ||
				second != this.second || millisec != this.millisec ||
				(this.ampm.length > 0 &&
				    (hour < 12) != ($.inArray(this.ampm.toUpperCase(), this.amNames) !== -1)) ||
				timezone != this.timezone);

		if (hasChanged) {

			if (hour !== false) { this.hour = hour; }
			if (minute !== false) { this.minute = minute; }
			if (second !== false) { this.second = second; }
			if (millisec !== false) { this.millisec = millisec; }
			if (timezone !== false) { this.timezone = timezone; }

			if (!this.inst) { this.inst = $.datepicker._getInst(this.$input[0]); }

			this._limitMinMaxDateTime(this.inst, true);
		}
		if (o.ampm) { this.ampm = ampm; }

		//this._formatTime();
		this.formattedTime = $.datepicker.formatTime(this._defaults.timeFormat, this, this._defaults);
		if (this.$timeObj) { this.$timeObj.text(this.formattedTime + o.timeSuffix); }
		this.timeDefined = true;
		if (hasChanged) { this._updateDateTime(); }
	},

	//########################################################################
	// call custom onSelect.
	// bind to sliders slidestop, and grid click.
	//########################################################################
	_onSelectHandler: function() {
		var onSelect = this._defaults.onSelect;
		var inputEl = this.$input ? this.$input[0] : null;
		if (onSelect && inputEl) {
			onSelect.apply(inputEl, [this.formattedDateTime, this]);
		}
	},

	//########################################################################
	// left for any backwards compatibility
	//########################################################################
	_formatTime: function(time, format) {
		time = time || { hour: this.hour, minute: this.minute, second: this.second, millisec: this.millisec, ampm: this.ampm, timezone: this.timezone };
		var tmptime = (format || this._defaults.timeFormat).toString();

		tmptime = $.datepicker.formatTime(tmptime, time, this._defaults);

		if (arguments.length) { return tmptime; }
		else { this.formattedTime = tmptime; }
	},

	//########################################################################
	// update our input with the new date time..
	//########################################################################
	_updateDateTime: function(dp_inst) {
		dp_inst = this.inst || dp_inst;
		var dt = $.datepicker._daylightSavingAdjust(new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay)),
			dateFmt = $.datepicker._get(dp_inst, 'dateFormat'),
			formatCfg = $.datepicker._getFormatConfig(dp_inst),
			timeAvailable = dt !== null && this.timeDefined;
		this.formattedDate = $.datepicker.formatDate(dateFmt, (dt === null ? new Date() : dt), formatCfg);
		var formattedDateTime = this.formattedDate;
		// remove following lines to force every changes in date picker to change the input value
		// Bug descriptions: when an input field has a default value, and click on the field to pop up the date picker. 
		// If the user manually empty the value in the input field, the date picker will never change selected value.
		//if (dp_inst.lastVal !== undefined && (dp_inst.lastVal.length > 0 && this.$input.val().length === 0)) {
		//	return;
        //}

		if (this._defaults.timeOnly === true) {
			formattedDateTime = this.formattedTime;
		} else if (this._defaults.timeOnly !== true && (this._defaults.alwaysSetTime || timeAvailable)) {
			formattedDateTime += this._defaults.separator + this.formattedTime + this._defaults.timeSuffix;
		}

		this.formattedDateTime = formattedDateTime;

		if(!this._defaults.showTimepicker) {
			this.$input.val(this.formattedDate);
		} else if (this.$altInput && this._defaults.altFieldTimeOnly === true) {
			this.$altInput.val(this.formattedTime);
			this.$input.val(this.formattedDate);
		} else if(this.$altInput) {
			this.$altInput.val(formattedDateTime);
			this.$input.val(formattedDateTime);
		} else {
			this.$input.val(formattedDateTime);
		}

		this.$input.trigger("change");
	}

});

$.fn.extend({
	//########################################################################
	// shorthand just to use timepicker..
	//########################################################################
	timepicker: function(o) {
		o = o || {};
		var tmp_args = arguments;

		if (typeof o == 'object') { tmp_args[0] = $.extend(o, { timeOnly: true }); }

		return $(this).each(function() {
			$.fn.datetimepicker.apply($(this), tmp_args);
		});
	},

	//########################################################################
	// extend timepicker to datepicker
	//########################################################################
	datetimepicker: function(o) {
		o = o || {};
		var tmp_args = arguments;

		if (typeof(o) == 'string'){
			if(o == 'getDate') {
				return $.fn.datepicker.apply($(this[0]), tmp_args);
            }
			else {
				return this.each(function() {
					var $t = $(this);
					$t.datepicker.apply($t, tmp_args);
				});
            }
		}
		else {
			return this.each(function() {
				var $t = $(this);
				$t.datepicker($.timepicker._newInst($t, o)._defaults);
			});
        }
	}
});

$.datepicker.parseDateTime = function(dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings) {
	var parseRes = parseDateTimeInternal(dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings);
	if (parseRes.timeObj)
	{
		var t = parseRes.timeObj;
		parseRes.date.setHours(t.hour, t.minute, t.second, t.millisec);
	}

	return parseRes.date;
};

$.datepicker.parseTime = function(timeFormat, timeString, options) {
	
	//########################################################################
	// pattern for standard and localized AM/PM markers
	//########################################################################
	var getPatternAmpm = function(amNames, pmNames) {
		var markers = [];
		if (amNames) {
			$.merge(markers, amNames);
        }
		if (pmNames) {
			$.merge(markers, pmNames);
        }
		markers = $.map(markers, function(val) { return val.replace(/[.*+?|()\[\]{}\\]/g, '\\$&'); });
		return '(' + markers.join('|') + ')?';
	};
   
	//########################################################################
	// figure out position of time elements.. cause js cant do named captures
	//########################################################################
	var getFormatPositions = function( timeFormat ) {
		var finds = timeFormat.toLowerCase().match(/(h{1,2}|m{1,2}|s{1,2}|l{1}|t{1,2}|z)/g),
			orders = { h: -1, m: -1, s: -1, l: -1, t: -1, z: -1 };

		if (finds) {
			for (var i = 0; i < finds.length; i++) {
				if (orders[finds[i].toString().charAt(0)] == -1) {
					orders[finds[i].toString().charAt(0)] = i + 1;
                }
            }
        }
		return orders;
	};
    
	var o = extendRemove(extendRemove({}, $.timepicker._defaults), options || {});
    
	var regstr = '^' + timeFormat.toString()
			.replace(/h{1,2}/ig, '(\\d?\\d)')
			.replace(/m{1,2}/ig, '(\\d?\\d)')
			.replace(/s{1,2}/ig, '(\\d?\\d)')
			.replace(/l{1}/ig, '(\\d?\\d?\\d)')
			.replace(/t{1,2}/ig, getPatternAmpm(o.amNames, o.pmNames))
			.replace(/z{1}/ig, '(z|[-+]\\d\\d:?\\d\\d)?')
			.replace(/\s/g, '\\s?') + o.timeSuffix + '$',
		order = getFormatPositions(timeFormat),
		ampm = '',
		treg;

	treg = timeString.match(new RegExp(regstr, 'i'));

	var resTime = {hour: 0, minute: 0, second: 0, millisec: 0};
    
	if (treg) {
		if (order.t !== -1) {
			if (treg[order.t] === undefined || treg[order.t].length === 0) {
				ampm = '';
				resTime.ampm = '';
			} else {
				ampm = $.inArray(treg[order.t], o.amNames) !== -1 ? 'AM' : 'PM';
				resTime.ampm = o[ampm == 'AM' ? 'amNames' : 'pmNames'][0];
			}
		}

		if (order.h !== -1) {
			if (ampm == 'AM' && treg[order.h] == '12') {
				resTime.hour = 0; // 12am = 0 hour
			} else {
                if (ampm == 'PM' && treg[order.h] != '12') {
                    resTime.hour = parseInt(treg[order.h],10) + 12; // 12pm = 12 hour, any other pm = hour + 12
                }
                else { resTime.hour = Number(treg[order.h]); }
            }
		}

		if (order.m !== -1) { resTime.minute = Number(treg[order.m]); }
		if (order.s !== -1) { resTime.second = Number(treg[order.s]); }
		if (order.l !== -1) { resTime.millisec = Number(treg[order.l]); }
		if (order.z !== -1 && treg[order.z] !== undefined) {
			var tz = treg[order.z].toUpperCase();
			switch (tz.length) {
				case 1:	// Z
					tz = o.timezoneIso8601 ? 'Z' : '+0000';
					break;
				case 5:	// +hhmm
					if (o.timezoneIso8601) {
						tz = tz.substring(1) == '0000' ?
							'Z' :
							tz.substring(0, 3) + ':' + tz.substring(3);
                    }
					break;
				case 6:	// +hh:mm
					if (!o.timezoneIso8601) {
						tz = tz == 'Z' || tz.substring(1) == '00:00' ?
							'+0000' :
							tz.replace(/:/, '');
					} else {
                        if (tz.substring(1) == '00:00') {
                            tz = 'Z';
                        }
                    }
					break;
			}
			resTime.timezone = tz;
		}


		return resTime;
	}

	return false;
};

//########################################################################
// format the time all pretty...
// format = string format of the time
// time = a {}, not a Date() for timezones
// options = essentially the regional[].. amNames, pmNames, ampm
//########################################################################
$.datepicker.formatTime = function(format, time, options) {
	options = options || {};
	options = $.extend($.timepicker._defaults, options);
	time = $.extend({hour:0, minute:0, second:0, millisec:0, timezone:'+0000'}, time);

	var tmptime = format;
	var ampmName = options.amNames[0];

	var hour = parseInt(time.hour, 10);
	if (options.ampm) {
		if (hour > 11){
			ampmName = options.pmNames[0];
			if(hour > 12) {
				hour = hour % 12;
            }
		}
		if (hour === 0) {
			hour = 12;
        }
	}
	tmptime = tmptime.replace(/(?:hh?|mm?|ss?|[tT]{1,2}|[lz])/g, function(match) {
		switch (match.toLowerCase()) {
			case 'hh': return ('0' + hour).slice(-2);
			case 'h':  return hour;
			case 'mm': return ('0' + time.minute).slice(-2);
			case 'm':  return time.minute;
			case 'ss': return ('0' + time.second).slice(-2);
			case 's':  return time.second;
			case 'l':  return ('00' + time.millisec).slice(-3);
			case 'z':  return time.timezone;
			case 't': case 'tt':
				if (options.ampm) {
					if (match.length == 1) {
						ampmName = ampmName.charAt(0);
                    }
					return match.charAt(0) == 'T' ? ampmName.toUpperCase() : ampmName.toLowerCase();
				}
				return '';
		}
	});

	tmptime = $.trim(tmptime);
	return tmptime;
};

//########################################################################
// the bad hack :/ override datepicker so it doesnt close on select
// inspired: http://stackoverflow.com/questions/1252512/jquery-datepicker-prevent-closing-picker-when-clicking-a-date/1762378#1762378
//########################################################################
$.datepicker._base_selectDate = $.datepicker._selectDate;
$.datepicker._selectDate = function (id, dateStr) {
	var inst = this._getInst($(id)[0]),
		tp_inst = this._get(inst, 'timepicker');

	if (tp_inst) {
		tp_inst._limitMinMaxDateTime(inst, true);
		inst.inline = inst.stay_open = true;
		//This way the onSelect handler called from calendarpicker get the full dateTime
		this._base_selectDate(id, dateStr);
		inst.inline = inst.stay_open = false;
		this._notifyChange(inst);
		this._updateDatepicker(inst);
	}
	else { this._base_selectDate(id, dateStr); }
};

//#############################################################################################
// second bad hack :/ override datepicker so it triggers an event when changing the input field
// and does not redraw the datepicker on every selectDate event
//#############################################################################################
$.datepicker._base_updateDatepicker = $.datepicker._updateDatepicker;
$.datepicker._updateDatepicker = function(inst) {

	// don't popup the datepicker if there is another instance already opened
	var input = inst.input[0];
	if($.datepicker._curInst &&
	   $.datepicker._curInst != inst &&
	   $.datepicker._datepickerShowing &&
	   $.datepicker._lastInput != input) {
		return;
	}

	if (typeof(inst.stay_open) !== 'boolean' || inst.stay_open === false) {

		this._base_updateDatepicker(inst);

		// Reload the time control when changing something in the input text field.
		var tp_inst = this._get(inst, 'timepicker');
		if(tp_inst) {
			tp_inst._addTimePicker(inst);

			if (tp_inst._defaults.useLocalTimezone) { //checks daylight saving with the new date.
				var date = new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay, 12);
				selectLocalTimeZone(tp_inst, date);
				tp_inst._onTimeChange();
			}
		}
	}
};

//#######################################################################################
// third bad hack :/ override datepicker so it allows spaces and colon in the input field
//#######################################################################################
$.datepicker._base_doKeyPress = $.datepicker._doKeyPress;
$.datepicker._doKeyPress = function(event) {
	var inst = $.datepicker._getInst(event.target),
		tp_inst = $.datepicker._get(inst, 'timepicker');

	if (tp_inst) {
		if ($.datepicker._get(inst, 'constrainInput')) {
			var ampm = tp_inst._defaults.ampm,
				dateChars = $.datepicker._possibleChars($.datepicker._get(inst, 'dateFormat')),
				datetimeChars = tp_inst._defaults.timeFormat.toString()
								.replace(/[hms]/g, '')
								.replace(/TT/g, ampm ? 'APM' : '')
								.replace(/Tt/g, ampm ? 'AaPpMm' : '')
								.replace(/tT/g, ampm ? 'AaPpMm' : '')
								.replace(/T/g, ampm ? 'AP' : '')
								.replace(/tt/g, ampm ? 'apm' : '')
								.replace(/t/g, ampm ? 'ap' : '') +
								" " +
								tp_inst._defaults.separator +
								tp_inst._defaults.timeSuffix +
								(tp_inst._defaults.showTimezone ? tp_inst._defaults.timezoneList.join('') : '') +
								(tp_inst._defaults.amNames.join('')) +
								(tp_inst._defaults.pmNames.join('')) +
								dateChars,
				chr = String.fromCharCode(event.charCode === undefined ? event.keyCode : event.charCode);
			return event.ctrlKey || (chr < ' ' || !dateChars || datetimeChars.indexOf(chr) > -1);
		}
	}

	return $.datepicker._base_doKeyPress(event);
};

//#######################################################################################
// Override key up event to sync manual input changes.
//#######################################################################################
$.datepicker._base_doKeyUp = $.datepicker._doKeyUp;
$.datepicker._doKeyUp = function (event) {
	var inst = $.datepicker._getInst(event.target),
		tp_inst = $.datepicker._get(inst, 'timepicker');

	if (tp_inst) {
		if (tp_inst._defaults.timeOnly && (inst.input.val() != inst.lastVal)) {
			try {
				$.datepicker._updateDatepicker(inst);
			}
			catch (err) {
				$.datepicker.log(err);
			}
		}
	}

	return $.datepicker._base_doKeyUp(event);
};

//#######################################################################################
// override "Today" button to also grab the time.
//#######################################################################################
$.datepicker._base_gotoToday = $.datepicker._gotoToday;
$.datepicker._gotoToday = function(id) {
	var inst = this._getInst($(id)[0]),
		$dp = inst.dpDiv;
	this._base_gotoToday(id);
	var tp_inst = this._get(inst, 'timepicker');
	selectLocalTimeZone(tp_inst);
	var now = new Date();
	this._setTime(inst, now);
	$( '.ui-datepicker-today', $dp).click();
};

//#######################################################################################
// Disable & enable the Time in the datetimepicker
//#######################################################################################
$.datepicker._disableTimepickerDatepicker = function(target) {
	var inst = this._getInst(target);
    if (!inst) { return; }
    
	var tp_inst = this._get(inst, 'timepicker');
	$(target).datepicker('getDate'); // Init selected[Year|Month|Day]
	if (tp_inst) {
		tp_inst._defaults.showTimepicker = false;
		tp_inst._updateDateTime(inst);
	}
};

$.datepicker._enableTimepickerDatepicker = function(target) {
	var inst = this._getInst(target);
    if (!inst) { return; }
    
	var tp_inst = this._get(inst, 'timepicker');
	$(target).datepicker('getDate'); // Init selected[Year|Month|Day]
	if (tp_inst) {
		tp_inst._defaults.showTimepicker = true;
		tp_inst._addTimePicker(inst); // Could be disabled on page load
		tp_inst._updateDateTime(inst);
	}
};

//#######################################################################################
// Create our own set time function
//#######################################################################################
$.datepicker._setTime = function(inst, date) {
	var tp_inst = this._get(inst, 'timepicker');
	if (tp_inst) {
		var defaults = tp_inst._defaults,
			// calling _setTime with no date sets time to defaults
			hour = date ? date.getHours() : defaults.hour,
			minute = date ? date.getMinutes() : defaults.minute,
			second = date ? date.getSeconds() : defaults.second,
			millisec = date ? date.getMilliseconds() : defaults.millisec;
		//check if within min/max times..
		// correct check if within min/max times. 	
		// Rewritten by Scott A. Woodward
		var hourEq = hour === defaults.hourMin,
			minuteEq = minute === defaults.minuteMin,
			secondEq = second === defaults.secondMin;
		var reset = false;
		if(hour < defaults.hourMin || hour > defaults.hourMax)  
			reset = true;
		else if( (minute < defaults.minuteMin || minute > defaults.minuteMax) && hourEq)
			reset = true;
		else if( (second < defaults.secondMin || second > defaults.secondMax ) && hourEq && minuteEq)
			reset = true;
		else if( (millisec < defaults.millisecMin || millisec > defaults.millisecMax) && hourEq && minuteEq && secondEq)
			reset = true;
		if(reset) {
			hour = defaults.hourMin;
			minute = defaults.minuteMin;
			second = defaults.secondMin;
			millisec = defaults.millisecMin;
		}
		tp_inst.hour = hour;
		tp_inst.minute = minute;
		tp_inst.second = second;
		tp_inst.millisec = millisec;
		if (tp_inst.hour_slider) tp_inst.hour_slider.slider('value', hour);
		if (tp_inst.minute_slider) tp_inst.minute_slider.slider('value', minute);
		if (tp_inst.second_slider) tp_inst.second_slider.slider('value', second);
		if (tp_inst.millisec_slider) tp_inst.millisec_slider.slider('value', millisec);

		tp_inst._onTimeChange();
		tp_inst._updateDateTime(inst);
	}
};

//#######################################################################################
// Create new public method to set only time, callable as $().datepicker('setTime', date)
//#######################################################################################
$.datepicker._setTimeDatepicker = function(target, date, withDate) {
	var inst = this._getInst(target);
    if (!inst) { return; }
    
	var tp_inst = this._get(inst, 'timepicker');
    
	if (tp_inst) {
		this._setDateFromField(inst);
		var tp_date;
		if (date) {
			if (typeof date == "string") {
				tp_inst._parseTime(date, withDate);
				tp_date = new Date();
				tp_date.setHours(tp_inst.hour, tp_inst.minute, tp_inst.second, tp_inst.millisec);
			}
			else { tp_date = new Date(date.getTime()); }
			if (tp_date.toString() == 'Invalid Date') { tp_date = undefined; }
			this._setTime(inst, tp_date);
		}
	}

};

//#######################################################################################
// override setDate() to allow setting time too within Date object
//#######################################################################################
$.datepicker._base_setDateDatepicker = $.datepicker._setDateDatepicker;
$.datepicker._setDateDatepicker = function(target, date) {
	var inst = this._getInst(target);
    if (!inst) { return; }
    
	var tp_date = (date instanceof Date) ? new Date(date.getTime()) : date;

	this._updateDatepicker(inst);
	this._base_setDateDatepicker.apply(this, arguments);
	this._setTimeDatepicker(target, tp_date, true);
};

//#######################################################################################
// override getDate() to allow getting time too within Date object
//#######################################################################################
$.datepicker._base_getDateDatepicker = $.datepicker._getDateDatepicker;
$.datepicker._getDateDatepicker = function(target, noDefault) {
	var inst = this._getInst(target);
    if (!inst) { return; }
    
    var tp_inst = this._get(inst, 'timepicker');

	if (tp_inst) {
		this._setDateFromField(inst, noDefault);
		var date = this._getDate(inst);
		if (date && tp_inst._parseTime($(target).val(), tp_inst.timeOnly)) { date.setHours(tp_inst.hour, tp_inst.minute, tp_inst.second, tp_inst.millisec); }
		return date;
	}
	return this._base_getDateDatepicker(target, noDefault);
};

//#######################################################################################
// override parseDate() because UI 1.8.14 throws an error about "Extra characters"
// An option in datapicker to ignore extra format characters would be nicer.
//#######################################################################################
$.datepicker._base_parseDate = $.datepicker.parseDate;
$.datepicker.parseDate = function(format, value, settings) {
    var splitRes = splitDateTime(format, value, settings);
	return $.datepicker._base_parseDate(format, splitRes[0], settings);
};

//#######################################################################################
// override formatDate to set date with time to the input
//#######################################################################################
$.datepicker._base_formatDate = $.datepicker._formatDate;
$.datepicker._formatDate = function(inst, day, month, year){
	var tp_inst = this._get(inst, 'timepicker');
	if(tp_inst) {
		tp_inst._updateDateTime(inst);
		return tp_inst.$input.val();
	}
	return this._base_formatDate(inst);
};

//#######################################################################################
// override options setter to add time to maxDate(Time) and minDate(Time). MaxDate
//#######################################################################################
$.datepicker._base_optionDatepicker = $.datepicker._optionDatepicker;
$.datepicker._optionDatepicker = function(target, name, value) {
	var inst = this._getInst(target);
    if (!inst) { return null; }
    
	var tp_inst = this._get(inst, 'timepicker');
	if (tp_inst) {
		var min = null, max = null, onselect = null;
		if (typeof name == 'string') { // if min/max was set with the string
			if (name === 'minDate' || name === 'minDateTime' ) {
				min = value;
            }
			else {
                if (name === 'maxDate' || name === 'maxDateTime') {
                    max = value;
                }
                else {
                    if (name === 'onSelect') {
                        onselect = value;
                    }
                }
            }
		} else {
            if (typeof name == 'object') { //if min/max was set with the JSON
                if (name.minDate) {
                    min = name.minDate;
                } else {
                    if (name.minDateTime) {
                        min = name.minDateTime;
                    } else {
                        if (name.maxDate) {
                            max = name.maxDate;
                        } else {
                            if (name.maxDateTime) {
                                max = name.maxDateTime;
                            }
                        }
                    }
                }
            }
        }
		if(min) { //if min was set
			if (min === 0) {
				min = new Date();
            } else {
				min = new Date(min);
            }

			tp_inst._defaults.minDate = min;
			tp_inst._defaults.minDateTime = min;
		} else if (max) { //if max was set
			if(max===0) {
				max=new Date();
            } else {
				max= new Date(max);
            }
			tp_inst._defaults.maxDate = max;
			tp_inst._defaults.maxDateTime = max;
		} else if (onselect) {
			tp_inst._defaults.onSelect = onselect;
        }
	}
	if (value === undefined) {
		return this._base_optionDatepicker(target, name);
    }
	return this._base_optionDatepicker(target, name, value);
};

//#######################################################################################
// jQuery extend now ignores nulls!
//#######################################################################################
function extendRemove(target, props) {
	$.extend(target, props);
	for (var name in props) {
		if (props[name] === null || props[name] === undefined) {
			target[name] = props[name];
        }
    }
	return target;
}

//#######################################################################################
// Splits datetime string into date ans time substrings.
// Throws exception when date can't be parsed
// If only date is present, time substring eill be '' 
//#######################################################################################
var splitDateTime = function(dateFormat, dateTimeString, dateSettings)
{
	try {
		var date = $.datepicker._base_parseDate(dateFormat, dateTimeString, dateSettings);
	} catch (err) {
		if (err.indexOf(":") >= 0) {
			// Hack!  The error message ends with a colon, a space, and
			// the "extra" characters.  We rely on that instead of
			// attempting to perfectly reproduce the parsing algorithm.
            var dateStringLength = dateTimeString.length-(err.length-err.indexOf(':')-2);
            var timeString = dateTimeString.substring(dateStringLength);

            return [dateTimeString.substring(0, dateStringLength), dateTimeString.substring(dateStringLength)];
            
		} else {
			throw err;
		}
	}
	return [dateTimeString, ''];
};

//#######################################################################################
// Internal function to parse datetime interval
// Returns: {date: Date, timeObj: Object}, where
//   date - parsed date without time (type Date)
//   timeObj = {hour: , minute: , second: , millisec: } - parsed time. Optional
//#######################################################################################
var parseDateTimeInternal = function(dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings)
{
    var date;
    var splitRes = splitDateTime(dateFormat, dateTimeString, dateSettings);
	date = $.datepicker._base_parseDate(dateFormat, splitRes[0], dateSettings);
    if (splitRes[1] !== '')
    {
        var timeString = splitRes[1];
        var separator = timeSettings && timeSettings.separator ? timeSettings.separator : $.timepicker._defaults.separator;            
        if ( timeString.indexOf(separator) !== 0) {
            throw 'Missing time separator';
        }
        timeString = timeString.substring(separator.length);
        var parsedTime = $.datepicker.parseTime(timeFormat, timeString, timeSettings);
        if (parsedTime === null) {
            throw 'Wrong time format';
        }
        return {date: date, timeObj: parsedTime};
    } else {
        return {date: date};
    }
};

//#######################################################################################
// Internal function to set timezone_select to the local timezone
//#######################################################################################
var selectLocalTimeZone = function(tp_inst, date)
{
	if (tp_inst && tp_inst.timezone_select) {
		tp_inst._defaults.useLocalTimezone = true;
		var now = typeof date !== 'undefined' ? date : new Date();
		var tzoffset = timeZoneString(now);
		if (tp_inst._defaults.timezoneIso8601) {
			tzoffset = tzoffset.substring(0, 3) + ':' + tzoffset.substring(3);
        }
		tp_inst.timezone_select.val(tzoffset);
	}
};

// Input: Date Object
// Output: String with timezone offset, e.g. '+0100'
var timeZoneString = function(date)
{
	var off = date.getTimezoneOffset() * -10100 / 60;
	var timezone = (off >= 0 ? '+' : '-') + Math.abs(off).toString().substr(1);
	return timezone;
};

$.timepicker = new Timepicker(); // singleton instance
$.timepicker.version = "1.0.1";

})(jQuery);

/*! 
* jQuery Array Utilities - v1.0
* https://github.com/KristianAbrahamsen/jquery.arrayUtilities
* Copyright (c) 2013 - Kristian Marheim Abrahamsen; Licensed MIT licence */

/********************************************************************
* jQuery Array Utilities
* MIT license
* Kristian Marheim Abrahamsen, 2013
* https://github.com/KristianAbrahamsen/jquery.arrayUtilities
*********************************************************************/

(function ($) {
    var plugin = {};

    var checkIfAllArgumentsAreArrays = function (functionArguments) {
        for (var i = 0; i < functionArguments.length; i++) {
            if (!(functionArguments[i] instanceof Array)) {
                throw new Error('Every argument must be an array!');
            }
        }
    }

    plugin.distinct = function (array) {
        if (arguments.length != 1) throw new Error('There must be exactly 1 array argument!');
        checkIfAllArgumentsAreArrays(arguments);

        var result = [];

        for (var i = 0; i < array.length; i++) {
            var item = array[i];

            if ($.inArray(item, result) === -1) {
                result.push(item);
            }
        }

        return result;
    }

    plugin.union = function (/* minimum 2 arrays */) {
        if (arguments.length < 2) throw new Error('There must be minimum 2 array arguments!');
        checkIfAllArgumentsAreArrays(arguments);

        var result = this.distinct(arguments[0]);

        for (var i = 1; i < arguments.length; i++) {
            var arrayArgument = arguments[i];

            for (var j = 0; j < arrayArgument.length; j++) {
                var item = arrayArgument[j];

                if ($.inArray(item, result) === -1) {
                    result.push(item);
                }
            }
        }

        return result;
    }

    plugin.intersect = function (/* minimum 2 arrays */) {
        if (arguments.length < 2) throw new Error('There must be minimum 2 array arguments!');
        checkIfAllArgumentsAreArrays(arguments);

        var result = [];
        var distinctArray = this.distinct(arguments[0]);
        if (distinctArray.length === 0) return [];

        for (var i = 0; i < distinctArray.length; i++) {
            var item = distinctArray[i];

            var shouldAddToResult = true;

            for (var j = 1; j < arguments.length; j++) {
                var array2 = arguments[j];
                if (array2.length == 0) return [];

                if ($.inArray(item, array2) === -1) {
                    shouldAddToResult = false;
                    break;
                }
            }

            if (shouldAddToResult) {
                result.push(item);
            }
        }

        return result;
    }

    plugin.except = function (/* minimum 2 arrays */) {
        if (arguments.length < 2) throw new Error('There must be minimum 2 array arguments!');
        checkIfAllArgumentsAreArrays(arguments);

        var result = [];
        var distinctArray = this.distinct(arguments[0]);
        var otherArraysConcatenated = [];

        for (var i = 1; i < arguments.length; i++) {
            var otherArray = arguments[i];
            otherArraysConcatenated = otherArraysConcatenated.concat(otherArray);
        }

        for (var i = 0; i < distinctArray.length; i++) {
            var item = distinctArray[i];

            if ($.inArray(item, otherArraysConcatenated) === -1) {
                result.push(item);
            }
        }

        return result;
    }

    $.arrayUtilities = plugin;

    $.distinct = plugin.distinct;
    $.union = plugin.union;
    $.intersect = plugin.intersect;
    $.except = plugin.except;
} (jQuery));

/*! 
* Input Mask plugin - v2.3.17
* http://github.com/RobinHerbots/jquery.inputmask
* Copyright (c) 2010 - 2013 Robin Herbots; Licensed MIT license */

/**
* Input Mask plugin for jquery
* http://github.com/RobinHerbots/jquery.inputmask
* Copyright (c) 2010 - 2013 Robin Herbots
* Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
* Version: 2.3.17
*/

(function ($) {
    if ($.fn.inputmask == undefined) {
        $.inputmask = {
            //options default
            defaults: {
                placeholder: "_",
                optionalmarker: {
                    start: "[",
                    end: "]"
                },
                escapeChar: "\\",
                mask: null,
                oncomplete: $.noop, //executes when the mask is complete
                onincomplete: $.noop, //executes when the mask is incomplete and focus is lost
                oncleared: $.noop, //executes when the mask is cleared
                repeat: 0, //repetitions of the mask: * ~ forever, otherwise specify an integer
                greedy: true, //true: allocated buffer for the mask and repetitions - false: allocate only if needed
                autoUnmask: false, //automatically unmask when retrieving the value with $.fn.val or value if the browser supports __lookupGetter__ or getOwnPropertyDescriptor
                clearMaskOnLostFocus: true,
                insertMode: true, //insert the input or overwrite the input
                clearIncomplete: false, //clear the incomplete input on blur
                aliases: {}, //aliases definitions => see jquery.inputmask.extensions.js
                onKeyUp: $.noop, //override to implement autocomplete on certain keys for example
                onKeyDown: $.noop, //override to implement autocomplete on certain keys for example
                showMaskOnFocus: true, //show the mask-placeholder when the input has focus
                showMaskOnHover: true, //show the mask-placeholder when hovering the empty input
                onKeyValidation: $.noop, //executes on every key-press with the result of isValid. Params: result, opts
                skipOptionalPartCharacter: " ", //a character which can be used to skip an optional part of a mask
                showTooltip: false, //show the activemask as tooltip
                numericInput: false, //numericInput input direction style (input shifts to the left while holding the caret position)
                //numeric basic properties
                isNumeric: false, //enable numeric features
                radixPoint: "", //".", // | ","
                skipRadixDance: false, //disable radixpoint caret positioning
                rightAlignNumerics: true, //align numerics to the right
                //numeric basic properties
                definitions: {
                    '9': {
                        validator: "[0-9]",
                        cardinality: 1
                    },
                    'a': {
                        validator: "[A-Za-z\u0410-\u044F\u0401\u0451]",
                        cardinality: 1
                    },
                    '*': {
                        validator: "[A-Za-z\u0410-\u044F\u0401\u04510-9]",
                        cardinality: 1
                    }
                },
                keyCode: {
                    ALT: 18, BACKSPACE: 8, CAPS_LOCK: 20, COMMA: 188, COMMAND: 91, COMMAND_LEFT: 91, COMMAND_RIGHT: 93, CONTROL: 17, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, INSERT: 45, LEFT: 37, MENU: 93, NUMPAD_ADD: 107, NUMPAD_DECIMAL: 110, NUMPAD_DIVIDE: 111, NUMPAD_ENTER: 108,
                    NUMPAD_MULTIPLY: 106, NUMPAD_SUBTRACT: 109, PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SHIFT: 16, SPACE: 32, TAB: 9, UP: 38, WINDOWS: 91
                },
                //specify keycodes which should not be considered in the keypress event, otherwise the preventDefault will stop their default behavior especially in FF
                ignorables: [9, 13, 19, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46, 93, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123],
                getMaskLength: function (buffer, greedy, repeat, currentBuffer, opts) {
                    var calculatedLength = buffer.length;
                    if (!greedy) {
                        if (repeat == "*") {
                            calculatedLength = currentBuffer.length + 1;
                        } else if (repeat > 1) {
                            calculatedLength += (buffer.length * (repeat - 1));
                        }
                    }
                    return calculatedLength;
                }
            },
            val: $.fn.val, //store the original jquery val function
            escapeRegex: function (str) {
                var specials = ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'];
                return str.replace(new RegExp('(\\' + specials.join('|\\') + ')', 'gim'), '\\$1');
            }
        };

        $.fn.inputmask = function (fn, options) {
            var opts = $.extend(true, {}, $.inputmask.defaults, options),
                msie10 = /*@cc_on!@*/false,
                iphone = navigator.userAgent.match(new RegExp("iphone", "i")) !== null,
                android = navigator.userAgent.match(new RegExp("android.*safari.*", "i")) !== null,
                pasteEvent = isInputEventSupported('paste') && !msie10 ? 'paste' : 'input',
                android53x,
                masksets,
                activeMasksetIndex = 0;

            if (android) {
                var browser = navigator.userAgent.match(/safari.*/i),
                    version = parseInt(new RegExp(/[0-9]+/).exec(browser));
                android53x = (version <= 537);
                //android534 = (533 < version) && (version <= 534);
            }
            if (typeof fn === "string") {
                switch (fn) {
                    case "mask":
                        //resolve possible aliases given by options
                        resolveAlias(opts.alias, options);
                        masksets = generateMaskSets();

                        return this.each(function () {
                            maskScope($.extend(true, {}, masksets), 0).mask(this);
                        });
                    case "unmaskedvalue":
                        var $input = $(this), input = this;
                        if ($input.data('_inputmask')) {
                            masksets = $input.data('_inputmask')['masksets'];
                            activeMasksetIndex = $input.data('_inputmask')['activeMasksetIndex'];
                            opts = $input.data('_inputmask')['opts'];
                            return maskScope(masksets, activeMasksetIndex).unmaskedvalue($input);
                        } else return $input.val();
                    case "remove":
                        return this.each(function () {
                            var $input = $(this), input = this;
                            if ($input.data('_inputmask')) {
                                masksets = $input.data('_inputmask')['masksets'];
                                activeMasksetIndex = $input.data('_inputmask')['activeMasksetIndex'];
                                opts = $input.data('_inputmask')['opts'];
                                //writeout the unmaskedvalue
                                input._valueSet(maskScope(masksets, activeMasksetIndex).unmaskedvalue($input, true));
                                //clear data
                                $input.removeData('_inputmask');
                                //unbind all events
                                $input.unbind(".inputmask");
                                $input.removeClass('focus.inputmask');
                                //restore the value property
                                var valueProperty;
                                if (Object.getOwnPropertyDescriptor)
                                    valueProperty = Object.getOwnPropertyDescriptor(input, "value");
                                if (valueProperty && valueProperty.get) {
                                    if (input._valueGet) {
                                        Object.defineProperty(input, "value", {
                                            get: input._valueGet,
                                            set: input._valueSet
                                        });
                                    }
                                } else if (document.__lookupGetter__ && input.__lookupGetter__("value")) {
                                    if (input._valueGet) {
                                        input.__defineGetter__("value", input._valueGet);
                                        input.__defineSetter__("value", input._valueSet);
                                    }
                                }
                                try { //try catch needed for IE7 as it does not supports deleting fns
                                    delete input._valueGet;
                                    delete input._valueSet;
                                } catch (e) {
                                    input._valueGet = undefined;
                                    input._valueSet = undefined;

                                }
                            }
                        });
                        break;
                    case "getemptymask": //return the default (empty) mask value, usefull for setting the default value in validation
                        if (this.data('_inputmask')) {
                            masksets = this.data('_inputmask')['masksets'];
                            activeMasksetIndex = this.data('_inputmask')['activeMasksetIndex'];
                            return masksets[activeMasksetIndex]['_buffer'].join('');
                        }
                        else return "";
                    case "hasMaskedValue": //check wheter the returned value is masked or not; currently only works reliable when using jquery.val fn to retrieve the value 
                        return this.data('_inputmask') ? !this.data('_inputmask')['opts'].autoUnmask : false;
                    case "isComplete":
                        masksets = this.data('_inputmask')['masksets'];
                        activeMasksetIndex = this.data('_inputmask')['activeMasksetIndex'];
                        opts = this.data('_inputmask')['opts'];
                        return maskScope(masksets, activeMasksetIndex).isComplete(this[0]._valueGet().split(''));
                    default:
                        //check if the fn is an alias
                        if (!resolveAlias(fn, options)) {
                            //maybe fn is a mask so we try
                            //set mask
                            opts.mask = fn;
                        }
                        masksets = generateMaskSets();

                        return this.each(function () {
                            maskScope($.extend(true, {}, masksets), activeMasksetIndex).mask(this);
                        });

                        break;
                }
            } else if (typeof fn == "object") {
                opts = $.extend(true, {}, $.inputmask.defaults, fn);

                resolveAlias(opts.alias, fn); //resolve aliases
                masksets = generateMaskSets();

                return this.each(function () {
                    maskScope($.extend(true, {}, masksets), activeMasksetIndex).mask(this);
                });
            } else if (fn == undefined) {
                //look for data-inputmask atribute - the attribute should only contain optipns
                return this.each(function () {
                    var attrOptions = $(this).attr("data-inputmask");
                    if (attrOptions && attrOptions != "") {
                        try {
                            attrOptions = attrOptions.replace(new RegExp("'", "g"), '"');
                            var dataoptions = $.parseJSON("{" + attrOptions + "}");
                            $.extend(true, dataoptions, options);
                            opts = $.extend(true, {}, $.inputmask.defaults, dataoptions);
                            resolveAlias(opts.alias, dataoptions);
                            opts.alias = undefined;
                            $(this).inputmask(opts);
                        } catch (ex) { } //need a more relax parseJSON
                    }
                });
            }

            //helper functions
            function isInputEventSupported(eventName) {
                var el = document.createElement('input'),
		        eventName = 'on' + eventName,
		        isSupported = (eventName in el);
                if (!isSupported) {
                    el.setAttribute(eventName, 'return;');
                    isSupported = typeof el[eventName] == 'function';
                }
                el = null;
                return isSupported;
            }
            function resolveAlias(aliasStr, options) {
                var aliasDefinition = opts.aliases[aliasStr];
                if (aliasDefinition) {
                    if (aliasDefinition.alias) resolveAlias(aliasDefinition.alias); //alias is another alias
                    $.extend(true, opts, aliasDefinition);  //merge alias definition in the options
                    $.extend(true, opts, options);  //reapply extra given options
                    return true;
                }
                return false;
            }
            function getMaskTemplate(mask) {
                var escaped = false, outCount = 0, greedy = opts.greedy, repeat = opts.repeat;
                if (repeat == "*") greedy = false;
                if (mask.length == 1 && greedy == false) { opts.placeholder = ""; } //hide placeholder with single non-greedy mask
                var singleMask = $.map(mask.split(""), function (element, index) {
                    var outElem = [];
                    if (element == opts.escapeChar) {
                        escaped = true;
                    }
                    else if ((element != opts.optionalmarker.start && element != opts.optionalmarker.end) || escaped) {
                        var maskdef = opts.definitions[element];
                        if (maskdef && !escaped) {
                            for (var i = 0; i < maskdef.cardinality; i++) {
                                outElem.push(getPlaceHolder(outCount + i));
                            }
                        } else {
                            outElem.push(element);
                            escaped = false;
                        }
                        outCount += outElem.length;
                        return outElem;
                    }
                });

                //allocate repetitions
                var repeatedMask = singleMask.slice();
                for (var i = 1; i < repeat && greedy; i++) {
                    repeatedMask = repeatedMask.concat(singleMask.slice());
                }

                return { "mask": repeatedMask, "repeat": repeat, "greedy": greedy };
            }
            //test definition => {fn: RegExp/function, cardinality: int, optionality: bool, newBlockMarker: bool, offset: int, casing: null/upper/lower, def: definitionSymbol}
            function getTestingChain(mask) {
                var isOptional = false, escaped = false;
                var newBlockMarker = false; //indicates wheter the begin/ending of a block should be indicated

                return $.map(mask.split(""), function (element, index) {
                    var outElem = [];

                    if (element == opts.escapeChar) {
                        escaped = true;
                    } else if (element == opts.optionalmarker.start && !escaped) {
                        isOptional = true;
                        newBlockMarker = true;
                    }
                    else if (element == opts.optionalmarker.end && !escaped) {
                        isOptional = false;
                        newBlockMarker = true;
                    }
                    else {
                        var maskdef = opts.definitions[element];
                        if (maskdef && !escaped) {
                            var prevalidators = maskdef["prevalidator"], prevalidatorsL = prevalidators ? prevalidators.length : 0;
                            for (var i = 1; i < maskdef.cardinality; i++) {
                                var prevalidator = prevalidatorsL >= i ? prevalidators[i - 1] : [], validator = prevalidator["validator"], cardinality = prevalidator["cardinality"];
                                outElem.push({ fn: validator ? typeof validator == 'string' ? new RegExp(validator) : new function () { this.test = validator; } : new RegExp("."), cardinality: cardinality ? cardinality : 1, optionality: isOptional, newBlockMarker: isOptional == true ? newBlockMarker : false, offset: 0, casing: maskdef["casing"], def: maskdef["definitionSymbol"] | element });
                                if (isOptional == true) //reset newBlockMarker
                                    newBlockMarker = false;
                            }
                            outElem.push({ fn: maskdef.validator ? typeof maskdef.validator == 'string' ? new RegExp(maskdef.validator) : new function () { this.test = maskdef.validator; } : new RegExp("."), cardinality: maskdef.cardinality, optionality: isOptional, newBlockMarker: newBlockMarker, offset: 0, casing: maskdef["casing"], def: maskdef["definitionSymbol"] | element });
                        } else {
                            outElem.push({ fn: null, cardinality: 0, optionality: isOptional, newBlockMarker: newBlockMarker, offset: 0, casing: null, def: element });
                            escaped = false;
                        }
                        //reset newBlockMarker
                        newBlockMarker = false;
                        return outElem;
                    }
                });
            }
            function generateMaskSets() {
                var ms = [];
                var genmasks = []; //used to keep track of the masks that where processed, to avoid duplicates
                function markOptional(maskPart) { //needed for the clearOptionalTail functionality
                    return opts.optionalmarker.start + maskPart + opts.optionalmarker.end;
                }
                function splitFirstOptionalEndPart(maskPart) {
                    var optionalStartMarkers = 0, optionalEndMarkers = 0, mpl = maskPart.length;
                    for (i = 0; i < mpl; i++) {
                        if (maskPart.charAt(i) == opts.optionalmarker.start) {
                            optionalStartMarkers++;
                        }
                        if (maskPart.charAt(i) == opts.optionalmarker.end) {
                            optionalEndMarkers++;
                        }
                        if (optionalStartMarkers > 0 && optionalStartMarkers == optionalEndMarkers)
                            break;
                    }
                    var maskParts = [maskPart.substring(0, i)];
                    if (i < mpl) {
                        maskParts.push(maskPart.substring(i + 1, mpl));
                    }
                    return maskParts;
                }
                function splitFirstOptionalStartPart(maskPart) {
                    var mpl = maskPart.length;
                    for (i = 0; i < mpl; i++) {
                        if (maskPart.charAt(i) == opts.optionalmarker.start) {
                            break;
                        }
                    }
                    var maskParts = [maskPart.substring(0, i)];
                    if (i < mpl) {
                        maskParts.push(maskPart.substring(i + 1, mpl));
                    }
                    return maskParts;
                }
                function generateMask(maskPrefix, maskPart) {
                    var maskParts = splitFirstOptionalEndPart(maskPart);
                    var newMask, maskTemplate;

                    var masks = splitFirstOptionalStartPart(maskParts[0]);
                    if (masks.length > 1) {
                        newMask = maskPrefix + masks[0] + markOptional(masks[1]) + (maskParts.length > 1 ? maskParts[1] : "");
                        if ($.inArray(newMask, genmasks) == -1) {
                            genmasks.push(newMask);
                            maskTemplate = getMaskTemplate(newMask);
                            ms.push({
                                "mask": newMask,
                                "_buffer": maskTemplate["mask"],
                                "buffer": maskTemplate["mask"].slice(),
                                "tests": getTestingChain(newMask),
                                "lastValidPosition": undefined,
                                "greedy": maskTemplate["greedy"],
                                "repeat": maskTemplate["repeat"]
                            });
                        }
                        newMask = maskPrefix + masks[0] + (maskParts.length > 1 ? maskParts[1] : "");
                        if ($.inArray(newMask, genmasks) == -1) {
                            genmasks.push(newMask);
                            maskTemplate = getMaskTemplate(newMask);
                            ms.push({
                                "mask": newMask,
                                "_buffer": maskTemplate["mask"],
                                "buffer": maskTemplate["mask"].slice(),
                                "tests": getTestingChain(newMask),
                                "lastValidPosition": undefined,
                                "greedy": maskTemplate["greedy"],
                                "repeat": maskTemplate["repeat"]
                            });
                        }
                        if (splitFirstOptionalStartPart(masks[1]).length > 1) { //optional contains another optional
                            generateMask(maskPrefix + masks[0], masks[1] + maskParts[1]);
                        }
                        if (maskParts.length > 1 && splitFirstOptionalStartPart(maskParts[1]).length > 1) {
                            generateMask(maskPrefix + masks[0] + markOptional(masks[1]), maskParts[1]);
                            generateMask(maskPrefix + masks[0], maskParts[1]);
                        }
                    }
                    else {
                        newMask = maskPrefix + maskParts;
                        if ($.inArray(newMask, genmasks) == -1) {
                            genmasks.push(newMask);
                            maskTemplate = getMaskTemplate(newMask);
                            ms.push({
                                "mask": newMask,
                                "_buffer": maskTemplate["mask"],
                                "buffer": maskTemplate["mask"].slice(),
                                "tests": getTestingChain(newMask),
                                "lastValidPosition": undefined,
                                "greedy": maskTemplate["greedy"],
                                "repeat": maskTemplate["repeat"]
                            });
                        }
                    }

                }
                if ($.isArray(opts.mask)) {
                    $.each(opts.mask, function (ndx, lmnt) {
                        generateMask("", lmnt.toString());
                    });
                } else generateMask("", opts.mask.toString());

                return opts.greedy ? ms : ms.sort(function (a, b) { return a["mask"].length - b["mask"].length; });
            }
            function getPlaceHolder(pos) {
                return opts.placeholder.charAt(pos % opts.placeholder.length);
            }

            function maskScope(masksets, activeMasksetIndex) {
                var isRTL = false;
                //maskset helperfunctions

                function getActiveMaskSet() {
                    return masksets[activeMasksetIndex];
                }

                function getActiveTests() {
                    return getActiveMaskSet()['tests'];
                }

                function getActiveBufferTemplate() {
                    return getActiveMaskSet()['_buffer'];
                }

                function getActiveBuffer() {
                    return getActiveMaskSet()['buffer'];
                }

                function isValid(pos, c, strict) { //strict true ~ no correction or autofill
                    strict = strict === true; //always set a value to strict to prevent possible strange behavior in the extensions 

                    function _isValid(position, activeMaskset) {
                        var testPos = determineTestPosition(position), loopend = c ? 1 : 0, chrs = '', buffer = activeMaskset["buffer"];
                        for (var i = activeMaskset['tests'][testPos].cardinality; i > loopend; i--) {
                            chrs += getBufferElement(buffer, testPos - (i - 1));
                        }

                        if (c) {
                            chrs += c;
                        }

                        //return is false or a json object => { pos: ??, c: ??} or true
                        return activeMaskset['tests'][testPos].fn != null ? activeMaskset['tests'][testPos].fn.test(chrs, buffer, position, strict, opts) : false;
                    }

                    if (strict) {
                        var result = _isValid(pos, getActiveMaskSet()); //only check validity in current mask when validating strict
                        if (result === true) {
                            result = { "pos": pos }; //always take a possible corrected maskposition into account
                        }
                        return result;
                    }

                    var results = [], result = false, currentActiveMasksetIndex = activeMasksetIndex;
                    $.each(masksets, function (index, value) {
                        if (typeof (value) == "object") {
                            activeMasksetIndex = index;

                            var maskPos = pos;
                            if (currentActiveMasksetIndex != activeMasksetIndex && !isMask(pos)) {
                                if (c == getActiveBufferTemplate()[maskPos] || c == opts.skipOptionalPartCharacter) { //match non-mask item
                                    results.push({ "activeMasksetIndex": index, "result": { "refresh": true, c: getActiveBufferTemplate()[maskPos] } }); //new command hack only rewrite buffer
                                    getActiveMaskSet()['lastValidPosition'] = maskPos;
                                    return false;
                                } else {
                                    if (masksets[currentActiveMasksetIndex]["lastValidPosition"] >= maskPos)
                                        getActiveMaskSet()['lastValidPosition'] = -1; //mark mask as validated and invalid
                                    else maskPos = seekNext(pos);
                                }

                            }
                            if ((getActiveMaskSet()['lastValidPosition'] == undefined
                                    && maskPos == seekNext(-1)
                            )
                                || getActiveMaskSet()['lastValidPosition'] >= seekPrevious(maskPos)) {
                                if (maskPos >= 0 && maskPos < getMaskLength()) {
                                    result = _isValid(maskPos, getActiveMaskSet());
                                    if (result !== false) {
                                        if (result === true) {
                                            result = { "pos": maskPos }; //always take a possible corrected maskposition into account
                                        }
                                        var newValidPosition = result.pos || maskPos;
                                        if (getActiveMaskSet()['lastValidPosition'] == undefined ||
                                            getActiveMaskSet()['lastValidPosition'] < newValidPosition)
                                            getActiveMaskSet()['lastValidPosition'] = newValidPosition; //set new position from isValid
                                    }
                                    results.push({ "activeMasksetIndex": index, "result": result });
                                }
                            }
                        }
                    });
                    activeMasksetIndex = currentActiveMasksetIndex; //reset activeMasksetIndex

                    return results; //return results of the multiple mask validations
                }

                function determineActiveMasksetIndex() {
                    var currentMasksetIndex = activeMasksetIndex,
                        highestValid = { "activeMasksetIndex": 0, "lastValidPosition": -1 };
                    $.each(masksets, function (index, value) {
                        if (typeof (value) == "object") {
                            var activeMaskset = this;
                            if (activeMaskset['lastValidPosition'] != undefined) {
                                if (activeMaskset['lastValidPosition'] > highestValid['lastValidPosition']) {
                                    highestValid["activeMasksetIndex"] = index;
                                    highestValid["lastValidPosition"] = activeMaskset['lastValidPosition'];
                                }
                            }
                        }
                    });
                    activeMasksetIndex = highestValid["activeMasksetIndex"];
                    if (currentMasksetIndex != activeMasksetIndex) {
                        clearBuffer(getActiveBuffer(), seekNext(highestValid["lastValidPosition"]), getMaskLength());
                        getActiveMaskSet()["writeOutBuffer"] = true;
                    }
                }

                function isMask(pos) {
                    var testPos = determineTestPosition(pos);
                    var test = getActiveTests()[testPos];

                    return test != undefined ? test.fn : false;
                }

                function determineTestPosition(pos) {
                    return pos % getActiveTests().length;
                }



                function getMaskLength() {
                    return opts.getMaskLength(getActiveBufferTemplate(), getActiveMaskSet()['greedy'], getActiveMaskSet()['repeat'], getActiveBuffer(), opts);
                }

                //pos: from position

                function seekNext(pos) {
                    var maskL = getMaskLength();
                    if (pos >= maskL) return maskL;
                    var position = pos;
                    while (++position < maskL && !isMask(position)) {
                    }
                    ;
                    return position;
                }

                //pos: from position

                function seekPrevious(pos) {
                    var position = pos;
                    if (position <= 0) return 0;

                    while (--position > 0 && !isMask(position)) {
                    }
                    ;
                    return position;
                }

                function setBufferElement(buffer, position, element, autoPrepare) {
                    if (autoPrepare) position = prepareBuffer(buffer, position);

                    var test = getActiveTests()[determineTestPosition(position)];
                    var elem = element;
                    if (elem != undefined) {
                        switch (test.casing) {
                            case "upper":
                                elem = element.toUpperCase();
                                break;
                            case "lower":
                                elem = element.toLowerCase();
                                break;
                        }
                    }

                    buffer[position] = elem;
                }

                function getBufferElement(buffer, position, autoPrepare) {
                    if (autoPrepare) position = prepareBuffer(buffer, position);
                    return buffer[position];
                }

                //needed to handle the non-greedy mask repetitions

                function prepareBuffer(buffer, position) {
                    var j;
                    while (buffer[position] == undefined && buffer.length < getMaskLength()) {
                        j = 0;
                        while (getActiveBufferTemplate()[j] !== undefined) { //add a new buffer
                            buffer.push(getActiveBufferTemplate()[j++]);
                        }
                    }

                    return position;
                }

                function writeBuffer(input, buffer, caretPos) {
                    input._valueSet(buffer.join(''));
                    if (caretPos != undefined) {
                        caret(input, caretPos);
                    }
                }

                ;

                function clearBuffer(buffer, start, end) {
                    for (var i = start, maskL = getMaskLength() ; i < end && i < maskL; i++) {
                        setBufferElement(buffer, i, getBufferElement(getActiveBufferTemplate().slice(), i, true));
                    }
                }

                ;

                function setReTargetPlaceHolder(buffer, pos) {
                    var testPos = determineTestPosition(pos);
                    setBufferElement(buffer, pos, getBufferElement(getActiveBufferTemplate(), testPos));
                }

                function checkVal(input, writeOut, strict, nptvl) {
                    var inputValue = nptvl != undefined ? nptvl.slice() : truncateInput(input._valueGet()).split('');

                    $.each(masksets, function (ndx, ms) {
                        if (typeof (ms) == "object") {
                            ms["buffer"] = ms["_buffer"].slice();
                            ms["lastValidPosition"] = undefined;
                            ms["p"] = 0;
                        }
                    });
                    if (strict !== true) activeMasksetIndex = 0;
                    if (writeOut) input._valueSet(""); //initial clear

                    var ml = getMaskLength();
                    $.each(inputValue, function (ndx, charCode) {
                        var index = ndx,
                            lvp = getActiveMaskSet()["lastValidPosition"],
                            pos = getActiveMaskSet()["p"];

                        pos = lvp == undefined ? index : pos;
                        lvp = lvp == undefined ? -1 : lvp;

                        if ((strict && isMask(index)) ||
                            ((charCode != getBufferElement(getActiveBufferTemplate().slice(), index, true) || isMask(index)) &&
                             $.inArray(charCode, getActiveBufferTemplate().slice(lvp + 1, pos)) == -1)
                            ) {
                            $(input).trigger("keypress", [true, charCode.charCodeAt(0), writeOut, strict, index]);
                        }
                    });
                    if (strict === true) {
                        getActiveMaskSet()["lastValidPosition"] = seekPrevious(getActiveMaskSet()["p"]);
                    }
                }

                function escapeRegex(str) {
                    return $.inputmask.escapeRegex.call(this, str);
                }

                function truncateInput(inputValue) {
                    return inputValue.replace(new RegExp("(" + escapeRegex(getActiveBufferTemplate().join('')) + ")*$"), "");
                }

                function clearOptionalTail(input) {
                    var buffer = getActiveBuffer(), tmpBuffer = buffer.slice(), testPos, pos;
                    for (var pos = tmpBuffer.length - 1; pos >= 0; pos--) {
                        var testPos = determineTestPosition(pos);
                        if (getActiveTests()[testPos].optionality) {
                            if (!isMask(pos) || !isValid(pos, buffer[pos], true))
                                tmpBuffer.pop();
                            else break;
                        } else break;
                    }
                    writeBuffer(input, tmpBuffer);
                }

                //functionality fn
                this.unmaskedvalue = function ($input, skipDatepickerCheck) {
                    isRTL = $input.data('_inputmask')['isRTL'];
                    return unmaskedvalue($input, skipDatepickerCheck);
                };
                function unmaskedvalue($input, skipDatepickerCheck) {
                    if (getActiveTests() && (skipDatepickerCheck === true || !$input.hasClass('hasDatepicker'))) {
                        //checkVal(input, false, true);
                        return $.map(getActiveBuffer(), function (element, index) {
                            return isMask(index) && isValid(index, element, true) ? element : null;
                        }).join('');
                    } else {
                        return $input[0]._valueGet();
                    }
                }

                function caret(input, begin, end, notranslate) {
                    function TranslatePosition(pos) {
                        if (notranslate !== true && isRTL && typeof pos == 'number') {
                            var bffrLght = getActiveBuffer().length;
                            pos = bffrLght - pos;
                        }
                        return pos;
                    }

                    var npt = input.jquery && input.length > 0 ? input[0] : input, range;
                    if (typeof begin == 'number') {
                        begin = TranslatePosition(begin); end = TranslatePosition(end);
                        if (!$(input).is(':visible')) {
                            return;
                        }
                        end = (typeof end == 'number') ? end : begin;
                        if (opts.insertMode == false && begin == end) end++; //set visualization for insert/overwrite mode
                        if (npt.setSelectionRange) {
                            npt.selectionStart = begin;
                            npt.selectionEnd = android ? begin : end;

                        } else if (npt.createTextRange) {
                            range = npt.createTextRange();
                            range.collapse(true);
                            range.moveEnd('character', end);
                            range.moveStart('character', begin);
                            range.select();
                        }
                    } else {
                        if (!$(input).is(':visible')) {
                            return { "begin": 0, "end": 0 };
                        }
                        if (npt.setSelectionRange) {
                            begin = npt.selectionStart;
                            end = npt.selectionEnd;
                        } else if (document.selection && document.selection.createRange) {
                            range = document.selection.createRange();
                            begin = 0 - range.duplicate().moveStart('character', -100000);
                            end = begin + range.text.length;
                        }
                        begin = TranslatePosition(begin); end = TranslatePosition(end);
                        return { "begin": begin, "end": end };
                    }
                };

                this.isComplete = function (buffer) {
                    return isComplete(buffer);
                };
                function isComplete(buffer) {
                    var complete = false, highestValidPosition = 0, currentActiveMasksetIndex = activeMasksetIndex;
                    $.each(masksets, function (ndx, ms) {
                        if (typeof (ms) == "object") {
                            activeMasksetIndex = ndx;
                            var aml = seekPrevious(getMaskLength());
                            if (ms["lastValidPosition"] != undefined && ms["lastValidPosition"] >= highestValidPosition && ms["lastValidPosition"] == aml) {
                                var msComplete = true;
                                for (var i = 0; i <= aml; i++) {
                                    var mask = isMask(i), testPos = determineTestPosition(i);
                                    if ((mask && (buffer[i] == undefined || buffer[i] == getPlaceHolder(i))) || (!mask && buffer[i] != getActiveBufferTemplate()[testPos])) {
                                        msComplete = false;
                                        break;
                                    }
                                }
                                complete = complete || msComplete;
                                if (complete) //break loop
                                    return false;
                            }
                            highestValidPosition = ms["lastValidPosition"];
                        }
                    });
                    activeMasksetIndex = currentActiveMasksetIndex; //reset activeMaskset
                    return complete;
                }

                function isSelection(begin, end) {
                    return isRTL ? (begin - end) > 1 || ((begin - end) == 1 && opts.insertMode) :
                            (end - begin) > 1 || ((end - begin) == 1 && opts.insertMode);
                }

                this.mask = function (el) {
                    var $input = $(el);
                    if (!$input.is(":input")) return;

                    //store tests & original buffer in the input element - used to get the unmasked value
                    $input.data('_inputmask', {
                        'masksets': masksets,
                        'activeMasksetIndex': activeMasksetIndex,
                        'opts': opts,
                        'isRTL': false
                    });

                    //show tooltip
                    if (opts.showTooltip) {
                        $input.prop("title", getActiveMaskSet()["mask"]);
                    }

                    //correct greedy setting if needed
                    getActiveMaskSet()['greedy'] = getActiveMaskSet()['greedy'] ? getActiveMaskSet()['greedy'] : getActiveMaskSet()['repeat'] == 0;

                    //handle maxlength attribute
                    if ($input.attr("maxLength") != null) //only when the attribute is set
                    {
                        var maxLength = $input.prop('maxLength');
                        if (maxLength > -1) { //handle *-repeat
                            $.each(masksets, function (ndx, ms) {
                                if (typeof (ms) == "object") {
                                    if (ms["repeat"] == "*") {
                                        ms["repeat"] = maxLength;
                                    }
                                }
                            });
                        }
                        if (getMaskLength() > maxLength && maxLength > -1) { //FF sets no defined max length to -1 
                            if (maxLength < getActiveBufferTemplate().length) getActiveBufferTemplate().length = maxLength;
                            if (getActiveMaskSet()['greedy'] == false) {
                                getActiveMaskSet()['repeat'] = Math.round(maxLength / getActiveBufferTemplate().length);
                            }
                            $input.prop('maxLength', getMaskLength() * 2);
                        }
                    }

                    patchValueProperty(el);

                    //init vars
                    getActiveMaskSet()["undoBuffer"] = el._valueGet();
                    var skipKeyPressEvent = false, //Safari 5.1.x - modal dialog fires keypress twice workaround
                        ignorable = false;
                    if (el.dir == "rtl" || (opts.numericInput && opts.rightAlignNumerics) || (opts.isNumeric && opts.rightAlignNumerics))
                        $input.css("text-align", "right");

                    if (el.dir == "rtl" || opts.numericInput) {
                        el.dir = "ltr";
                        $input.removeAttr("dir");
                        var inputData = $input.data('_inputmask');
                        inputData['isRTL'] = true;
                        $input.data('_inputmask', inputData);
                        isRTL = true;
                    }

                    //unbind all events - to make sure that no other mask will interfere when re-masking
                    $input.unbind(".inputmask");
                    $input.removeClass('focus.inputmask');
                    //bind events
                    $input.closest('form').bind("submit", function () { //trigger change on submit if any
                        if ($input[0]._valueGet && $input[0]._valueGet() != getActiveMaskSet()["undoBuffer"]) {
                            $input.change();
                        }
                    }).bind('reset', function () {
                        $.each(masksets, function (ndx, ms) {
                            if (typeof (ms) == "object") {
                                ms["buffer"] = ms["_buffer"].slice();
                                ms["lastValidPosition"] = undefined;
                                ms["p"] = -1;
                            }
                        });
                    });
                    $input.bind("mouseenter.inputmask", function () {
                        var $input = $(this), input = this;
                        if (!$input.hasClass('focus.inputmask') && opts.showMaskOnHover) {
                            if (input._valueGet() != getActiveBuffer().join('')) {
                                writeBuffer(input, getActiveBuffer());
                            }
                        }
                    }).bind("blur.inputmask", function () {
                        var $input = $(this), input = this, nptValue = input._valueGet(), buffer = getActiveBuffer();
                        $input.removeClass('focus.inputmask');
                        if (nptValue != getActiveMaskSet()["undoBuffer"]) {
                            $input.change();
                        }
                        if (opts.clearMaskOnLostFocus && nptValue != '') {
                            if (nptValue == getActiveBufferTemplate().join(''))
                                input._valueSet('');
                            else { //clearout optional tail of the mask
                                clearOptionalTail(input);
                            }
                        }
                        if (!isComplete(buffer)) {
                            $input.trigger("incomplete");
                            if (opts.clearIncomplete) {
                                $.each(masksets, function (ndx, ms) {
                                    if (typeof (ms) == "object") {
                                        ms["buffer"] = ms["_buffer"].slice();
                                        ms["lastValidPosition"] = undefined;
                                        ms["p"] = 0;
                                    }
                                });
                                activeMasksetIndex = 0;
                                if (opts.clearMaskOnLostFocus)
                                    input._valueSet('');
                                else {
                                    buffer = getActiveBufferTemplate().slice();
                                    writeBuffer(input, buffer);
                                }
                            }
                        }
                    }).bind("focus.inputmask", function () {
                        var $input = $(this), input = this, nptValue = input._valueGet();
                        if (opts.showMaskOnFocus && !$input.hasClass('focus.inputmask') && (!opts.showMaskOnHover || (opts.showMaskOnHover && nptValue == ''))) {
                            if (input._valueGet() != getActiveBuffer().join('')) {
                                writeBuffer(input, getActiveBuffer(), getActiveMaskSet()["p"]);
                            }
                        }
                        $input.addClass('focus.inputmask');
                        getActiveMaskSet()["undoBuffer"] = input._valueGet();
                        $input.click();
                    }).bind("mouseleave.inputmask", function () {
                        var $input = $(this), input = this;
                        if (opts.clearMaskOnLostFocus) {
                            if (!$input.hasClass('focus.inputmask')) {
                                if (input._valueGet() == getActiveBufferTemplate().join('') || input._valueGet() == '')
                                    input._valueSet('');
                                else { //clearout optional tail of the mask
                                    clearOptionalTail(input);
                                }
                            }
                        }
                    }).bind("click.inputmask", function () {
                        var input = this;
                        setTimeout(function () {
                            var selectedCaret = caret(input), buffer = getActiveBuffer();
                            if (selectedCaret.begin == selectedCaret.end) {
                                var clickPosition = selectedCaret.begin,
                                    lvp = getActiveMaskSet()["lastValidPosition"],
                                    lastPosition;
                                if (opts.isNumeric) {
                                    lastPosition = opts.skipRadixDance === false && opts.radixPoint != "" && $.inArray(opts.radixPoint, buffer) != -1 ? $.inArray(opts.radixPoint, buffer) : getMaskLength();
                                } else {
                                    lastPosition = seekNext(lvp == undefined ? -1 : lvp);
                                }
                                caret(input, clickPosition < lastPosition && (isValid(clickPosition, buffer[clickPosition], true) !== false || !isMask(clickPosition)) ? clickPosition : lastPosition);
                            }
                        }, 0);
                    }).bind('dblclick.inputmask', function () {
                        var input = this;
                        if (getActiveMaskSet()["lastValidPosition"] != undefined) {
                            setTimeout(function () {
                                caret(input, 0, seekNext(getActiveMaskSet()["lastValidPosition"]));
                            }, 0);
                        }
                    }).bind("keydown.inputmask", keydownEvent
                    ).bind("keypress.inputmask", keypressEvent
                    ).bind("keyup.inputmask", keyupEvent
                    ).bind(pasteEvent + ".inputmask dragdrop.inputmask drop.inputmask", function () {
                        var input = this, $input = $(input);
                        setTimeout(function () {
                            checkVal(input, true, false);
                            if (isComplete(getActiveBuffer()))
                                $input.trigger("complete");
                            $input.click();
                        }, 0);
                    }).bind('setvalue.inputmask', function () {
                        var input = this;
                        getActiveMaskSet()["undoBuffer"] = input._valueGet();
                        checkVal(input, true);
                        if (input._valueGet() == getActiveBufferTemplate().join(''))
                            input._valueSet('');
                    }).bind('complete.inputmask', opts.oncomplete)
                        .bind('incomplete.inputmask', opts.onincomplete)
                        .bind('cleared.inputmask', opts.oncleared);

                    //apply mask
                    checkVal(el, true, false);
                    // Wrap document.activeElement in a try/catch block since IE9 throw "Unspecified error" if document.activeElement is undefined when we are in an IFrame.
                    var activeElement;
                    try {
                        activeElement = document.activeElement;
                    } catch (e) {
                    }
                    if (activeElement === el) { //position the caret when in focus
                        $input.addClass('focus.inputmask');
                        caret(el, getActiveMaskSet()["p"]);
                    } else if (opts.clearMaskOnLostFocus) {
                        if (getActiveBuffer().join('') == getActiveBufferTemplate().join('')) {
                            el._valueSet('');
                        } else {
                            clearOptionalTail(el);
                        }
                    } else {
                        writeBuffer(el, getActiveBuffer());
                    }

                    installEventRuler(el);

                    //private functions

                    function installEventRuler(npt) {
                        var events = $._data(npt).events;

                        $.each(events, function (eventType, eventHandlers) {
                            $.each(eventHandlers, function (ndx, eventHandler) {
                                if (eventHandler.namespace == "inputmask") {
                                    var handler = eventHandler.handler;
                                    eventHandler.handler = function (e) {
                                        if (this.readOnly || this.disabled)
                                            e.preventDefault;
                                        else
                                            return handler.apply(this, arguments);
                                    };
                                }
                            });
                        });
                    }

                    function patchValueProperty(npt) {
                        var valueProperty;
                        if (Object.getOwnPropertyDescriptor)
                            valueProperty = Object.getOwnPropertyDescriptor(npt, "value");
                        if (valueProperty && valueProperty.get) {
                            if (!npt._valueGet) {
                                var valueGet = valueProperty.get;
                                var valueSet = valueProperty.set;
                                npt._valueGet = function () {
                                    return isRTL ? valueGet.call(this).split('').reverse().join('') : valueGet.call(this);
                                };
                                npt._valueSet = function (value) {
                                    valueSet.call(this, isRTL ? value.split('').reverse().join('') : value);
                                };

                                Object.defineProperty(npt, "value", {
                                    get: function () {
                                        var $self = $(this), inputData = $(this).data('_inputmask'), masksets = inputData['masksets'],
                                            activeMasksetIndex = inputData['activeMasksetIndex'];
                                        return inputData && inputData['opts'].autoUnmask ? $self.inputmask('unmaskedvalue') : this._valueGet() != masksets[activeMasksetIndex]['_buffer'].join('') ? this._valueGet() : '';
                                    },
                                    set: function (value) {
                                        this._valueSet(value);
                                        $(this).triggerHandler('setvalue.inputmask');
                                    }
                                });
                            }
                        } else if (document.__lookupGetter__ && npt.__lookupGetter__("value")) {
                            if (!npt._valueGet) {
                                var valueGet = npt.__lookupGetter__("value");
                                var valueSet = npt.__lookupSetter__("value");
                                npt._valueGet = function () {
                                    return isRTL ? valueGet.call(this).split('').reverse().join('') : valueGet.call(this);
                                };
                                npt._valueSet = function (value) {
                                    valueSet.call(this, isRTL ? value.split('').reverse().join('') : value);
                                };

                                npt.__defineGetter__("value", function () {
                                    var $self = $(this), inputData = $(this).data('_inputmask'), masksets = inputData['masksets'],
                                        activeMasksetIndex = inputData['activeMasksetIndex'];
                                    return inputData && inputData['opts'].autoUnmask ? $self.inputmask('unmaskedvalue') : this._valueGet() != masksets[activeMasksetIndex]['_buffer'].join('') ? this._valueGet() : '';
                                });
                                npt.__defineSetter__("value", function (value) {
                                    this._valueSet(value);
                                    $(this).triggerHandler('setvalue.inputmask');
                                });
                            }
                        } else {
                            if (!npt._valueGet) {
                                npt._valueGet = function () { return isRTL ? this.value.split('').reverse().join('') : this.value; };
                                npt._valueSet = function (value) { this.value = isRTL ? value.split('').reverse().join('') : value; };
                            }
                            if ($.fn.val.inputmaskpatch != true) {
                                $.fn.val = function () {
                                    if (arguments.length == 0) {
                                        var $self = $(this);
                                        if ($self.data('_inputmask')) {
                                            if ($self.data('_inputmask')['opts'].autoUnmask)
                                                return $self.inputmask('unmaskedvalue');
                                            else {
                                                var result = $.inputmask.val.apply($self);
                                                var inputData = $(this).data('_inputmask'), masksets = inputData['masksets'],
                                                    activeMasksetIndex = inputData['activeMasksetIndex'];
                                                return result != masksets[activeMasksetIndex]['_buffer'].join('') ? result : '';
                                            }
                                        } else return $.inputmask.val.apply($self);
                                    } else {
                                        var args = arguments;
                                        return this.each(function () {
                                            var $self = $(this);
                                            var result = $.inputmask.val.apply($self, args);
                                            if ($self.data('_inputmask')) $self.triggerHandler('setvalue.inputmask');
                                            return result;
                                        });
                                    }
                                };
                                $.extend($.fn.val, {
                                    inputmaskpatch: true
                                });
                            }
                        }
                    }

                    //shift chars to left from start to end and put c at end position if defined
                    function shiftL(start, end, c) {
                        var buffer = getActiveBuffer();
                        while (!isMask(start) && start - 1 >= 0) start--; //jumping over nonmask position
                        for (var i = start; i < end && i < getMaskLength() ; i++) {
                            if (isMask(i)) {
                                setReTargetPlaceHolder(buffer, i);
                                var j = seekNext(i);
                                var p = getBufferElement(buffer, j);
                                if (p != getPlaceHolder(j)) {
                                    if (j < getMaskLength() && isValid(i, p, true) !== false && getActiveTests()[determineTestPosition(i)].def == getActiveTests()[determineTestPosition(j)].def) {
                                        setBufferElement(buffer, i, getBufferElement(buffer, j), true);
                                        if (j < end) {
                                            setReTargetPlaceHolder(buffer, j); //cleanup next position
                                        }
                                    } else {
                                        if (isMask(i))
                                            break;
                                    }
                                } //else if (c == undefined) break;
                            } else {
                                setReTargetPlaceHolder(buffer, i);
                            }
                        }
                        if (c != undefined)
                            setBufferElement(buffer, seekPrevious(end), c);

                        if (getActiveMaskSet()["greedy"] == false) {
                            var trbuffer = truncateInput(buffer.join('')).split('');
                            buffer.length = trbuffer.length;
                            for (var i = 0, bl = buffer.length; i < bl; i++) {
                                buffer[i] = trbuffer[i];
                            }
                            if (buffer.length == 0) getActiveMaskSet()["buffer"] = getActiveBufferTemplate().slice();
                        }
                        return start; //return the used start position
                    }

                    function shiftR(start, end, c, full) { //full => behave like a push right ~ do not stop on placeholders
                        var buffer = getActiveBuffer();
                        for (var i = start; i <= end && i < getMaskLength() ; i++) {
                            if (isMask(i)) {
                                var t = getBufferElement(buffer, i, true);
                                setBufferElement(buffer, i, c, true);
                                if (t != getPlaceHolder(i)) {
                                    var j = seekNext(i);
                                    if (j < getMaskLength()) {
                                        if (isValid(j, t, true) !== false && getActiveTests()[determineTestPosition(i)].def == getActiveTests()[determineTestPosition(j)].def)
                                            c = t;
                                        else {
                                            if (isMask(j))
                                                break;
                                            else c = t;
                                        }
                                    } else break;
                                } else {
                                    c = t;
                                    if (full !== true) break;
                                }
                            } else
                                setReTargetPlaceHolder(buffer, i);
                        }
                        var lengthBefore = buffer.length;
                        if (getActiveMaskSet()["greedy"] == false) {
                            var trbuffer = truncateInput(buffer.join('')).split('');
                            buffer.length = trbuffer.length;
                            for (var i = 0, bl = buffer.length; i < bl; i++) {
                                buffer[i] = trbuffer[i];
                            }
                            if (buffer.length == 0) getActiveMaskSet()["buffer"] = getActiveBufferTemplate().slice();
                        }
                        return end - (lengthBefore - buffer.length); //return new start position
                    }

                    ;

                    function keydownEvent(e) {
                        //Safari 5.1.x - modal dialog fires keypress twice workaround
                        skipKeyPressEvent = false;
                        var input = this, k = e.keyCode, pos = caret(input);

                        //backspace, delete, and escape get special treatment
                        if (k == opts.keyCode.BACKSPACE || k == opts.keyCode.DELETE || (iphone && k == 127) || (e.ctrlKey && k == 88)) { //backspace/delete
                            e.preventDefault(); //stop default action but allow propagation

                            if (opts.numericInput || isRTL) {
                                switch (k) {
                                    case opts.keyCode.BACKSPACE:
                                        k = opts.keyCode.DELETE;
                                        break;
                                    case opts.keyCode.DELETE:
                                        k = opts.keyCode.BACKSPACE;
                                        break;
                                }
                            }

                            if (isSelection(pos.begin, pos.end)) {
                                if (isRTL) {
                                    var pend = pos.end;
                                    pos.end = pos.begin;
                                    pos.begin = pend;
                                }
                                clearBuffer(getActiveBuffer(), pos.begin, pos.end);
                                if (pos.begin == 0 && pos.end == getMaskLength()) {
                                    $.each(masksets, function (ndx, ms) {
                                        if (typeof (ms) == "object") {
                                            ms["buffer"] = ms["_buffer"].slice();
                                            ms["lastValidPosition"] = undefined;
                                            ms["p"] = 0;
                                        }
                                    });
                                } else { //partial selection
                                    var ml = getMaskLength();
                                    if (opts.greedy == false) {
                                        shiftL(pos.begin, ml);
                                    } else {
                                        for (var i = pos.begin; i < pos.end; i++) {
                                            if (isMask(i))
                                                shiftL(pos.begin, ml);
                                        }
                                    }
                                    checkVal(input, false, true, getActiveBuffer());
                                }
                            } else {
                                $.each(masksets, function (ndx, ms) {
                                    if (typeof (ms) == "object") {
                                        activeMasksetIndex = ndx;
                                        var beginPos = android53x ? pos.end : pos.begin;
                                        var buffer = getActiveBuffer(), firstMaskPos = seekNext(-1),
                                            maskL = getMaskLength();
                                        if (k == opts.keyCode.DELETE) { //handle delete
                                            if (beginPos < firstMaskPos)
                                                beginPos = firstMaskPos;
                                            if (beginPos < maskL) {
                                                if (opts.isNumeric && opts.radixPoint != "" && buffer[beginPos] == opts.radixPoint) {
                                                    beginPos = (buffer.length - 1 == beginPos) /* radixPoint is latest? delete it */ ? beginPos : seekNext(beginPos);
                                                    beginPos = shiftL(beginPos, maskL);
                                                } else {
                                                    beginPos = shiftL(beginPos, maskL);
                                                }
                                                if (getActiveMaskSet()['lastValidPosition'] != undefined) {
                                                    if (getActiveMaskSet()['lastValidPosition'] != -1 && getActiveBuffer()[getActiveMaskSet()['lastValidPosition']] == getActiveBufferTemplate()[getActiveMaskSet()['lastValidPosition']])
                                                        getActiveMaskSet()["lastValidPosition"] = getActiveMaskSet()["lastValidPosition"] == 0 ? -1 : seekPrevious(getActiveMaskSet()["lastValidPosition"]);
                                                    if (getActiveMaskSet()['lastValidPosition'] < firstMaskPos) {
                                                        getActiveMaskSet()["lastValidPosition"] = undefined;
                                                        getActiveMaskSet()["p"] = firstMaskPos;
                                                    } else {
                                                        getActiveMaskSet()["writeOutBuffer"] = true;
                                                        getActiveMaskSet()["p"] = beginPos;
                                                    }
                                                }
                                            }
                                        } else if (k == opts.keyCode.BACKSPACE) { //handle backspace
                                            if (beginPos > firstMaskPos) {
                                                beginPos -= 1;
                                                if (opts.isNumeric && opts.radixPoint != "" && buffer[beginPos] == opts.radixPoint) {
                                                    beginPos = shiftR(0, (buffer.length - 1 == beginPos) /* radixPoint is latest? delete it */ ? beginPos : beginPos - 1, getPlaceHolder(beginPos), true);
                                                    beginPos++;
                                                } else {
                                                    beginPos = shiftL(beginPos, maskL);
                                                }
                                                if (getActiveMaskSet()['lastValidPosition'] != undefined) {
                                                    if (getActiveMaskSet()['lastValidPosition'] != -1 && getActiveBuffer()[getActiveMaskSet()['lastValidPosition']] == getActiveBufferTemplate()[getActiveMaskSet()['lastValidPosition']])
                                                        getActiveMaskSet()["lastValidPosition"] = getActiveMaskSet()["lastValidPosition"] == 0 ? -1 : seekPrevious(getActiveMaskSet()["lastValidPosition"]);
                                                    if (getActiveMaskSet()['lastValidPosition'] < firstMaskPos) {
                                                        getActiveMaskSet()["lastValidPosition"] = undefined;
                                                        getActiveMaskSet()["p"] = firstMaskPos;
                                                    } else {
                                                        getActiveMaskSet()["writeOutBuffer"] = true;
                                                        getActiveMaskSet()["p"] = beginPos;
                                                    }
                                                }
                                            } else if (activeMasksetIndex > 0) { //retry other masks
                                                getActiveMaskSet()["lastValidPosition"] = undefined;
                                                getActiveMaskSet()["writeOutBuffer"] = true;
                                                getActiveMaskSet()["p"] = firstMaskPos;
                                                //init first 
                                                activeMasksetIndex = 0;
                                                getActiveMaskSet()["buffer"] = getActiveBufferTemplate().slice();
                                                getActiveMaskSet()["p"] = seekNext(-1);
                                                getActiveMaskSet()["lastValidPosition"] = undefined;
                                            }
                                        }
                                    }
                                });

                            }

                            determineActiveMasksetIndex();
                            writeBuffer(input, getActiveBuffer(), getActiveMaskSet()["p"]);
                            if (input._valueGet() == getActiveBufferTemplate().join(''))
                                $(input).trigger('cleared');

                            if (opts.showTooltip) { //update tooltip
                                $input.prop("title", getActiveMaskSet()["mask"]);
                            }
                        } else if (k == opts.keyCode.END || k == opts.keyCode.PAGE_DOWN) { //when END or PAGE_DOWN pressed set position at lastmatch
                            setTimeout(function () {
                                var caretPos = seekNext(getActiveMaskSet()["lastValidPosition"]);
                                if (!opts.insertMode && caretPos == getMaskLength() && !e.shiftKey) caretPos--;
                                caret(input, e.shiftKey ? pos.begin : caretPos, caretPos);
                            }, 0);
                        } else if ((k == opts.keyCode.HOME && !e.shiftKey) || k == opts.keyCode.PAGE_UP) { //Home or page_up
                            caret(input, 0, e.shiftKey ? pos.begin : 0);
                        } else if (k == opts.keyCode.ESCAPE) { //escape
                            input._valueSet(getActiveMaskSet()["undoBuffer"]);
                            checkVal(input, true, true);
                        } else if (k == opts.keyCode.INSERT && !(e.shiftKey || e.ctrlKey)) { //insert
                            opts.insertMode = !opts.insertMode;
                            caret(input, !opts.insertMode && pos.begin == getMaskLength() ? pos.begin - 1 : pos.begin);
                        } else if (opts.insertMode == false && !e.shiftKey) {
                            if (k == opts.keyCode.RIGHT) {
                                setTimeout(function () {
                                    var caretPos = caret(input);
                                    caret(input, caretPos.begin);
                                }, 0);
                            } else if (k == opts.keyCode.LEFT) {
                                setTimeout(function () {
                                    var caretPos = caret(input);
                                    caret(input, caretPos.begin - 1);
                                }, 0);
                            }
                        }
                        var caretPos = caret(input);
                        opts.onKeyDown.call(this, e, getActiveBuffer(), opts); //extra stuff to execute on keydown
                        caret(input, caretPos.begin, caretPos.end);
                        ignorable = $.inArray(k, opts.ignorables) != -1;
                    }

                    function keypressEvent(e, checkval, k, writeOut, strict, ndx) {
                        //Safari 5.1.x - modal dialog fires keypress twice workaround
                        if (k == undefined && skipKeyPressEvent) return false;
                        skipKeyPressEvent = true;

                        var input = this, $input = $(input);

                        e = e || window.event;
                        var k = k || e.which || e.charCode || e.keyCode,
                            c = String.fromCharCode(k);

                        if ((!(e.ctrlKey && e.altKey) && (e.ctrlKey || e.metaKey || ignorable)) && checkval !== true) {
                            return true;
                        } else {
                            if (k) {
                                var pos, results, result;
                                if (checkval) {
                                    var pcaret = strict ? ndx : getActiveMaskSet()["p"];
                                    pos = { begin: pcaret, end: pcaret };
                                } else {
                                    pos = caret(input);
                                }

                                //should we clear a possible selection??
                                var isSlctn = isSelection(pos.begin, pos.end), redetermineLVP = false;
                                if (isSlctn) {
                                    if (isRTL) {
                                        var pend = pos.end;
                                        pos.end = pos.begin;
                                        pos.begin = pend;
                                    }
                                    var initialIndex = activeMasksetIndex;
                                    $.each(masksets, function (ndx, lmnt) {
                                        if (typeof (lmnt) == "object") {
                                            activeMasksetIndex = ndx;
                                            getActiveMaskSet()["undoBuffer"] = getActiveBuffer().join(''); //init undobuffer for recovery when not valid
                                            var posend = pos.end < getMaskLength() ? pos.end : getMaskLength();
                                            if (getActiveMaskSet()["lastValidPosition"] > pos.begin && getActiveMaskSet()["lastValidPosition"] < posend) {
                                                getActiveMaskSet()["lastValidPosition"] = seekPrevious(pos.begin);
                                            } else {
                                                redetermineLVP = true;
                                            }
                                            clearBuffer(getActiveBuffer(), pos.begin, posend);
                                            var ml = getMaskLength();
                                            if (opts.greedy == false) {
                                                shiftL(pos.begin, ml);
                                            } else {
                                                for (var i = pos.begin; i < posend; i++) {
                                                    if (isMask(i))
                                                        shiftL(pos.begin, ml);
                                                }
                                            }
                                        }
                                    });
                                    if (redetermineLVP === true) {
                                        activeMasksetIndex = initialIndex;
                                        checkVal(input, false, true, getActiveBuffer());
                                        if (!opts.insertMode) { //preserve some space
                                            $.each(masksets, function (ndx, lmnt) {
                                                if (typeof (lmnt) == "object") {
                                                    activeMasksetIndex = ndx;
                                                    shiftR(pos.begin, getMaskLength(), getPlaceHolder(pos.begin), true);
                                                    getActiveMaskSet()["lastValidPosition"] = seekNext(getActiveMaskSet()["lastValidPosition"]);
                                                }
                                            });
                                        }
                                    }
                                    activeMasksetIndex = initialIndex; //restore index
                                }

                                if (opts.isNumeric && c == opts.radixPoint && checkval !== true) {
                                    var nptStr = getActiveBuffer().join('');
                                    var radixPosition = nptStr.indexOf(opts.radixPoint);
                                    if (radixPosition != -1) {
                                        pos.begin = pos.begin == radixPosition ? seekNext(radixPosition) : radixPosition;
                                        pos.end = pos.begin;
                                        caret(input, pos.begin);
                                    }
                                }

                                var p = (opts.numericInput && strict != true && !isSlctn) ? seekPrevious(pos.begin) : seekNext(pos.begin - 1);
                                results = isValid(p, c, strict);
                                if (strict === true) results = [{ "activeMasksetIndex": activeMasksetIndex, "result": results }];
                                $.each(results, function (index, result) {
                                    activeMasksetIndex = result["activeMasksetIndex"];
                                    getActiveMaskSet()["writeOutBuffer"] = true;
                                    var np = result["result"];
                                    if (np !== false) {
                                        var refresh = false, buffer = getActiveBuffer();
                                        if (np !== true) {
                                            refresh = np["refresh"]; //only rewrite buffer from isValid
                                            p = np.pos != undefined ? np.pos : p; //set new position from isValid
                                            c = np.c != undefined ? np.c : c; //set new char from isValid
                                        }
                                        if (refresh !== true) {
                                            if (opts.insertMode == true) {
                                                var lastUnmaskedPosition = getMaskLength();
                                                var bfrClone = buffer.slice();
                                                while (getBufferElement(bfrClone, lastUnmaskedPosition, true) != getPlaceHolder(lastUnmaskedPosition) && lastUnmaskedPosition >= p) {
                                                    lastUnmaskedPosition = lastUnmaskedPosition == 0 ? -1 : seekPrevious(lastUnmaskedPosition);
                                                }
                                                if (lastUnmaskedPosition >= p) {
                                                    shiftR(p, buffer.length, c);
                                                    //shift the lvp if needed
                                                    var lvp = getActiveMaskSet()["lastValidPosition"], nlvp = seekNext(lvp);
                                                    if (nlvp != getMaskLength() && lvp >= p && (getBufferElement(getActiveBuffer(), nlvp) != getPlaceHolder(nlvp))) {
                                                        getActiveMaskSet()["lastValidPosition"] = nlvp;
                                                    }
                                                } else getActiveMaskSet()["writeOutBuffer"] = false;
                                            } else setBufferElement(buffer, p, c, true);
                                        }
                                        getActiveMaskSet()["p"] = seekNext(p);
                                    }
                                });

                                if (strict !== true) determineActiveMasksetIndex();
                                if (writeOut !== false) {
                                    $.each(results, function (ndx, rslt) {
                                        if (rslt["activeMasksetIndex"] == activeMasksetIndex) {
                                            result = rslt;
                                            return false;
                                        }
                                    });
                                    if (result != undefined) {
                                        var self = this;
                                        setTimeout(function () { opts.onKeyValidation.call(self, result["result"], opts); }, 0);
                                        if (getActiveMaskSet()["writeOutBuffer"] && result["result"] !== false) {
                                            var buffer = getActiveBuffer();
                                            writeBuffer(input, buffer, checkval ? undefined : (opts.numericInput ? seekPrevious(getActiveMaskSet()["p"]) : getActiveMaskSet()["p"]));
                                            if (checkval !== true) {
                                                setTimeout(function () { //timeout needed for IE
                                                    if (isComplete(buffer))
                                                        $input.trigger("complete");
                                                }, 0);
                                            }
                                        } else if (isSlctn) {
                                            getActiveMaskSet()["buffer"] = getActiveMaskSet()["undoBuffer"].split('');
                                        }
                                    }
                                }

                                if (opts.showTooltip) { //update tooltip
                                    $input.prop("title", getActiveMaskSet()["mask"]);
                                }
                                e.preventDefault();
                            }
                        }
                    }

                    function keyupEvent(e) {
                        var $input = $(this), input = this, k = e.keyCode, buffer = getActiveBuffer();
                        var caretPos = caret(input);
                        opts.onKeyUp.call(this, e, buffer, opts); //extra stuff to execute on keyup
                        caret(input, caretPos.begin, caretPos.end);
                        if (k == opts.keyCode.TAB && $input.hasClass('focus.inputmask') && input._valueGet().length == 0 && opts.showMaskOnFocus) {
                            buffer = getActiveBufferTemplate().slice();
                            writeBuffer(input, buffer);
                            caret(input, 0);
                            getActiveMaskSet()["undoBuffer"] = input._valueGet();
                        }
                    }
                };
                return this;
            };
            return this;
        };
    }
})(jQuery);
/*
Input Mask plugin extensions
http://github.com/RobinHerbots/jquery.inputmask
Copyright (c) 2010 - 2013 Robin Herbots
Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
Version: 2.3.17

Optional extensions on the jquery.inputmask base
*/
(function ($) {
    //extra definitions
    $.extend($.inputmask.defaults.definitions, {
        'A': { 
            validator: "[A-Za-z]",
            cardinality: 1,
            casing: "upper" //auto uppercasing
        },
        '#': {
            validator: "[A-Za-z\u0410-\u044F\u0401\u04510-9]",
            cardinality: 1,
            casing: "upper"
        }
    });
    $.extend($.inputmask.defaults.aliases, {
        'url': {
            mask: "ir",
            placeholder: "",
            separator: "",
            defaultPrefix: "http://",
            regex: {
                urlpre1: new RegExp("[fh]"),
                urlpre2: new RegExp("(ft|ht)"),
                urlpre3: new RegExp("(ftp|htt)"),
                urlpre4: new RegExp("(ftp:|http|ftps)"),
                urlpre5: new RegExp("(ftp:/|ftps:|http:|https)"),
                urlpre6: new RegExp("(ftp://|ftps:/|http:/|https:)"),
                urlpre7: new RegExp("(ftp://|ftps://|http://|https:/)"),
                urlpre8: new RegExp("(ftp://|ftps://|http://|https://)")
            },
            definitions: {
                'i': {
                    validator: function (chrs, buffer, pos, strict, opts) {
                        return true;
                    },
                    cardinality: 8,
                    prevalidator: (function () {
                        var result = [], prefixLimit = 8;
                        for (var i = 0; i < prefixLimit; i++) {
                            result[i] = (function () {
                                var j = i;
                                return {
                                    validator: function (chrs, buffer, pos, strict, opts) {
                                        if (opts.regex["urlpre" + (j + 1)]) {
                                            var tmp = chrs, k;
                                            if (((j + 1) - chrs.length) > 0) {
                                                tmp = buffer.join('').substring(0, ((j + 1) - chrs.length)) + "" + tmp;
                                            }
                                            var isValid = opts.regex["urlpre" + (j + 1)].test(tmp);
                                            if (!strict && !isValid) {
                                                pos = pos - j;
                                                for (k = 0; k < opts.defaultPrefix.length; k++) {
                                                    buffer[pos] = opts.defaultPrefix[k]; pos++;
                                                }
                                                for (k = 0; k < tmp.length - 1; k++) {
                                                    buffer[pos] = tmp[k]; pos++;
                                                }
                                                return { "pos": pos };
                                            }
                                            return isValid;
                                        } else {
                                            return false;
                                        }
                                    }, cardinality: j
                                };
                            })();
                        }
                        return result;
                    })()
                },
                "r": {
                    validator: ".",
                    cardinality: 50
                }
            },
            insertMode: false,
            autoUnmask: false
        },
        "ip": {
            mask: "i.i.i.i",
            definitions: {
                'i': {
                    validator: "25[0-5]|2[0-4][0-9]|[01][0-9][0-9]",
                    cardinality: 3,
                    prevalidator: [
                                { validator: "[0-2]", cardinality: 1 },
                                { validator: "2[0-5]|[01][0-9]", cardinality: 2 }
                    ]
                }
            }
        }
    });
})(jQuery);
/*
Input Mask plugin extensions
http://github.com/RobinHerbots/jquery.inputmask
Copyright (c) 2010 - 2012 Robin Herbots
Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
Version: 2.3.17

Optional extensions on the jquery.inputmask base
*/
(function ($) {
    //date & time aliases
    $.extend($.inputmask.defaults.definitions, {
        'h': { //hours
            validator: "[01][0-9]|2[0-3]",
            cardinality: 2,
            prevalidator: [{ validator: "[0-2]", cardinality: 1 }]
        },
        's': { //seconds || minutes
            validator: "[0-5][0-9]",
            cardinality: 2,
            prevalidator: [{ validator: "[0-5]", cardinality: 1 }]
        },
        'd': { //basic day
            validator: "0[1-9]|[12][0-9]|3[01]",
            cardinality: 2,
            prevalidator: [{ validator: "[0-3]", cardinality: 1 }]
        },
        'm': { //basic month
            validator: "0[1-9]|1[012]",
            cardinality: 2,
            prevalidator: [{ validator: "[01]", cardinality: 1 }]
        },
        'y': { //basic year
            validator: "(19|20)\\d{2}",
            cardinality: 4,
            prevalidator: [
                        { validator: "[12]", cardinality: 1 },
                        { validator: "(19|20)", cardinality: 2 },
                        { validator: "(19|20)\\d", cardinality: 3 }
            ]
        }
    });
    $.extend($.inputmask.defaults.aliases, {
        'dd/mm/yyyy': {
            mask: "1/2/y",
            placeholder: "dd/mm/yyyy",
            regex: {
                val1pre: new RegExp("[0-3]"), //daypre
                val1: new RegExp("0[1-9]|[12][0-9]|3[01]"), //day
                val2pre: function (separator) { var escapedSeparator = $.inputmask.escapeRegex.call(this, separator); return new RegExp("((0[1-9]|[12][0-9]|3[01])" + escapedSeparator + "[01])"); }, //monthpre
                val2: function (separator) { var escapedSeparator = $.inputmask.escapeRegex.call(this, separator); return new RegExp("((0[1-9]|[12][0-9])" + escapedSeparator + "(0[1-9]|1[012]))|(30" + escapedSeparator + "(0[13-9]|1[012]))|(31" + escapedSeparator + "(0[13578]|1[02]))"); }//month
            },
            leapday: "29/02/",
            separator: '/',
            yearrange: { minyear: 1900, maxyear: 2099 },
            isInYearRange: function (chrs, minyear, maxyear) {
                var enteredyear = parseInt(chrs.concat(minyear.toString().slice(chrs.length)));
                var enteredyear2 = parseInt(chrs.concat(maxyear.toString().slice(chrs.length)));
                return (enteredyear != NaN ? minyear <= enteredyear && enteredyear <= maxyear : false) ||
            		   (enteredyear2 != NaN ? minyear <= enteredyear2 && enteredyear2 <= maxyear : false);
            },
            determinebaseyear: function (minyear, maxyear, hint) {
                var currentyear = (new Date()).getFullYear();
                if (minyear > currentyear) return minyear;
                if (maxyear < currentyear) {
                    var maxYearPrefix = maxyear.toString().slice(0, 2);
                    var maxYearPostfix = maxyear.toString().slice(2, 4);
                    while (maxyear < maxYearPrefix + hint) {
                        maxYearPrefix--;
                    }
                    var maxxYear = maxYearPrefix + maxYearPostfix;
                    return minyear > maxxYear ? minyear : maxxYear;
                }

                return currentyear;
            },
            onKeyUp: function (e, buffer, opts) {
                var $input = $(this);
                if (e.ctrlKey && e.keyCode == opts.keyCode.RIGHT) {
                    var today = new Date();
                    $input.val(today.getDate().toString() + (today.getMonth() + 1).toString() + today.getFullYear().toString());
                }
            },
            definitions: {
                '1': { //val1 ~ day or month
                    validator: function (chrs, buffer, pos, strict, opts) {
                        var isValid = opts.regex.val1.test(chrs);
                        if (!strict && !isValid) {
                            if (chrs.charAt(1) == opts.separator || "-./".indexOf(chrs.charAt(1)) != -1) {
                                isValid = opts.regex.val1.test("0" + chrs.charAt(0));
                                if (isValid) {
                                    buffer[pos - 1] = "0";
                                    return { "pos": pos, "c": chrs.charAt(0) };
                                }
                            }
                        }
                        return isValid;
                    },
                    cardinality: 2,
                    prevalidator: [{
                        validator: function (chrs, buffer, pos, strict, opts) {
                            var isValid = opts.regex.val1pre.test(chrs);
                            if (!strict && !isValid) {
                                isValid = opts.regex.val1.test("0" + chrs);
                                if (isValid) {
                                    buffer[pos] = "0";
                                    pos++;
                                    return { "pos": pos };
                                }
                            }
                            return isValid;
                        }, cardinality: 1
                    }]
                },
                '2': { //val2 ~ day or month
                    validator: function (chrs, buffer, pos, strict, opts) {
                        var frontValue = buffer.join('').substr(0, 3);
                        var isValid = opts.regex.val2(opts.separator).test(frontValue + chrs);
                        if (!strict && !isValid) {
                            if (chrs.charAt(1) == opts.separator || "-./".indexOf(chrs.charAt(1)) != -1) {
                                isValid = opts.regex.val2(opts.separator).test(frontValue + "0" + chrs.charAt(0));
                                if (isValid) {
                                    buffer[pos - 1] = "0";
                                    return { "pos": pos, "c": chrs.charAt(0) };
                                }
                            }
                        }
                        return isValid;
                    },
                    cardinality: 2,
                    prevalidator: [{
                        validator: function (chrs, buffer, pos, strict, opts) {
                            var frontValue = buffer.join('').substr(0, 3);
                            var isValid = opts.regex.val2pre(opts.separator).test(frontValue + chrs);
                            if (!strict && !isValid) {
                                isValid = opts.regex.val2(opts.separator).test(frontValue + "0" + chrs);
                                if (isValid) {
                                    buffer[pos] = "0";
                                    pos++;
                                    return { "pos": pos };
                                }
                            }
                            return isValid;
                        }, cardinality: 1
                    }]
                },
                'y': { //year
                    validator: function (chrs, buffer, pos, strict, opts) {
                        if (opts.isInYearRange(chrs, opts.yearrange.minyear, opts.yearrange.maxyear)) {
                            var dayMonthValue = buffer.join('').substr(0, 6);
                            if (dayMonthValue != opts.leapday)
                                return true;
                            else {
                                var year = parseInt(chrs, 10);//detect leap year
                                if (year % 4 === 0)
                                    if (year % 100 === 0)
                                        if (year % 400 === 0)
                                            return true;
                                        else return false;
                                    else return true;
                                else return false;
                            }
                        } else return false;
                    },
                    cardinality: 4,
                    prevalidator: [
                {
                    validator: function (chrs, buffer, pos, strict, opts) {
                        var isValid = opts.isInYearRange(chrs, opts.yearrange.minyear, opts.yearrange.maxyear);
                        if (!strict && !isValid) {
                            var yearPrefix = opts.determinebaseyear(opts.yearrange.minyear, opts.yearrange.maxyear, chrs + "0").toString().slice(0, 1);

                            isValid = opts.isInYearRange(yearPrefix + chrs, opts.yearrange.minyear, opts.yearrange.maxyear);
                            if (isValid) {
                                buffer[pos++] = yearPrefix[0];
                                return { "pos": pos };
                            }
                            yearPrefix = opts.determinebaseyear(opts.yearrange.minyear, opts.yearrange.maxyear, chrs + "0").toString().slice(0, 2);

                            isValid = opts.isInYearRange(yearPrefix + chrs, opts.yearrange.minyear, opts.yearrange.maxyear);
                            if (isValid) {
                                buffer[pos++] = yearPrefix[0];
                                buffer[pos++] = yearPrefix[1];
                                return { "pos": pos };
                            }
                        }
                        return isValid;
                    },
                    cardinality: 1
                },
                {
                    validator: function (chrs, buffer, pos, strict, opts) {
                        var isValid = opts.isInYearRange(chrs, opts.yearrange.minyear, opts.yearrange.maxyear);
                        if (!strict && !isValid) {
                            var yearPrefix = opts.determinebaseyear(opts.yearrange.minyear, opts.yearrange.maxyear, chrs).toString().slice(0, 2);

                            isValid = opts.isInYearRange(chrs[0] + yearPrefix[1] + chrs[1], opts.yearrange.minyear, opts.yearrange.maxyear);
                            if (isValid) {
                                buffer[pos++] = yearPrefix[1];
                                return { "pos": pos };
                            }

                            yearPrefix = opts.determinebaseyear(opts.yearrange.minyear, opts.yearrange.maxyear, chrs).toString().slice(0, 2);
                            if (opts.isInYearRange(yearPrefix + chrs, opts.yearrange.minyear, opts.yearrange.maxyear)) {
                                var dayMonthValue = buffer.join('').substr(0, 6);
                                if (dayMonthValue != opts.leapday)
                                    isValid = true;
                                else {
                                    var year = parseInt(chrs, 10);//detect leap year
                                    if (year % 4 === 0)
                                        if (year % 100 === 0)
                                            if (year % 400 === 0)
                                                isValid = true;
                                            else isValid = false;
                                        else isValid = true;
                                    else isValid = false;
                                }
                            } else isValid = false;
                            if (isValid) {
                                buffer[pos - 1] = yearPrefix[0];
                                buffer[pos++] = yearPrefix[1];
                                buffer[pos++] = chrs[0];
                                return { "pos": pos };
                            }
                        }
                        return isValid;
                    }, cardinality: 2
                },
                {
                    validator: function (chrs, buffer, pos, strict, opts) {
                        return opts.isInYearRange(chrs, opts.yearrange.minyear, opts.yearrange.maxyear);
                    }, cardinality: 3
                }
                    ]
                }
            },
            insertMode: false,
            autoUnmask: false
        },
        'mm/dd/yyyy': {
            placeholder: "mm/dd/yyyy",
            alias: "dd/mm/yyyy", //reuse functionality of dd/mm/yyyy alias
            regex: {
                val2pre: function (separator) { var escapedSeparator = $.inputmask.escapeRegex.call(this, separator); return new RegExp("((0[13-9]|1[012])" + escapedSeparator + "[0-3])|(02" + escapedSeparator + "[0-2])"); }, //daypre
                val2: function (separator) { var escapedSeparator = $.inputmask.escapeRegex.call(this, separator); return new RegExp("((0[1-9]|1[012])" + escapedSeparator + "(0[1-9]|[12][0-9]))|((0[13-9]|1[012])" + escapedSeparator + "30)|((0[13578]|1[02])" + escapedSeparator + "31)"); }, //day
                val1pre: new RegExp("[01]"), //monthpre
                val1: new RegExp("0[1-9]|1[012]") //month
            },
            leapday: "02/29/",
            onKeyUp: function (e, buffer, opts) {
                var $input = $(this);
                if (e.ctrlKey && e.keyCode == opts.keyCode.RIGHT) {
                    var today = new Date();
                    $input.val((today.getMonth() + 1).toString() + today.getDate().toString() + today.getFullYear().toString());
                }
            }
        },
        'yyyy/mm/dd': {
            mask: "y/1/2",
            placeholder: "yyyy/mm/dd",
            alias: "mm/dd/yyyy",
            leapday: "/02/29",
            onKeyUp: function (e, buffer, opts) {
                var $input = $(this);
                if (e.ctrlKey && e.keyCode == opts.keyCode.RIGHT) {
                    var today = new Date();
                    $input.val(today.getFullYear().toString() + (today.getMonth() + 1).toString() + today.getDate().toString());
                }
            },
            definitions: {
                '2': { //val2 ~ day or month
                    validator: function (chrs, buffer, pos, strict, opts) {
                        var frontValue = buffer.join('').substr(5, 3);
                        var isValid = opts.regex.val2(opts.separator).test(frontValue + chrs);
                        if (!strict && !isValid) {
                            if (chrs.charAt(1) == opts.separator || "-./".indexOf(chrs.charAt(1)) != -1) {
                                isValid = opts.regex.val2(opts.separator).test(frontValue + "0" + chrs.charAt(0));
                                if (isValid) {
                                    buffer[pos - 1] = "0";
                                    return { "pos": pos, "c": chrs.charAt(0) };
                                }
                            }
                        }

                        //check leap yeap
                        if (isValid) {
                            var dayMonthValue = buffer.join('').substr(4, 4) + chrs;
                            if (dayMonthValue != opts.leapday)
                                return true;
                            else {
                                var year = parseInt(buffer.join('').substr(0, 4), 10);  //detect leap year
                                if (year % 4 === 0)
                                    if (year % 100 === 0)
                                        if (year % 400 === 0)
                                            return true;
                                        else return false;
                                    else return true;
                                else return false;
                            }
                        }

                        return isValid;
                    },
                    cardinality: 2,
                    prevalidator: [{
                        validator: function (chrs, buffer, pos, strict, opts) {
                            var frontValue = buffer.join('').substr(5, 3);
                            var isValid = opts.regex.val2pre(opts.separator).test(frontValue + chrs);
                            if (!strict && !isValid) {
                                isValid = opts.regex.val2(opts.separator).test(frontValue + "0" + chrs);
                                if (isValid) {
                                    buffer[pos] = "0";
                                    pos++;
                                    return { "pos": pos };
                                }
                            }
                            return isValid;
                        }, cardinality: 1
                    }]
                }
            }
        },
        'dd.mm.yyyy': {
            mask: "1.2.y",
            placeholder: "dd.mm.yyyy",
            leapday: "29.02.",
            separator: '.',
            alias: "dd/mm/yyyy"
        },
        'dd-mm-yyyy': {
            mask: "1-2-y",
            placeholder: "dd-mm-yyyy",
            leapday: "29-02-",
            separator: '-',
            alias: "dd/mm/yyyy"
        },
        'mm.dd.yyyy': {
            mask: "1.2.y",
            placeholder: "mm.dd.yyyy",
            leapday: "02.29.",
            separator: '.',
            alias: "mm/dd/yyyy"
        },
        'mm-dd-yyyy': {
            mask: "1-2-y",
            placeholder: "mm-dd-yyyy",
            leapday: "02-29-",
            separator: '-',
            alias: "mm/dd/yyyy"
        },
        'yyyy.mm.dd': {
            mask: "y.1.2",
            placeholder: "yyyy.mm.dd",
            leapday: ".02.29",
            separator: '.',
            alias: "yyyy/mm/dd"
        },
        'yyyy-mm-dd': {
            mask: "y-1-2",
            placeholder: "yyyy-mm-dd",
            leapday: "-02-29",
            separator: '-',
            alias: "yyyy/mm/dd"
        },
        'datetime': {
            mask: "1/2/y h:s",
            placeholder: "dd/mm/yyyy hh:mm",
            alias: "dd/mm/yyyy",
            regex: {
                hrspre: new RegExp("[012]"), //hours pre
                hrs24: new RegExp("2[0-9]|1[3-9]"),
                hrs: new RegExp("[01][0-9]|2[0-3]"), //hours
                ampm: new RegExp("^[a|p|A|P][m|M]")
            },
            timeseparator: ':',
            hourFormat: "24", // or 12
            definitions: {
                'h': { //hours
                    validator: function (chrs, buffer, pos, strict, opts) {
                        var isValid = opts.regex.hrs.test(chrs);
                        if (!strict && !isValid) {
                            if (chrs.charAt(1) == opts.timeseparator || "-.:".indexOf(chrs.charAt(1)) != -1) {
                                isValid = opts.regex.hrs.test("0" + chrs.charAt(0));
                                if (isValid) {
                                    buffer[pos - 1] = "0";
                                    buffer[pos] = chrs.charAt(0);
                                    pos++;
                                    return { "pos": pos };
                                }
                            }
                        }

                        if (isValid && opts.hourFormat !== "24" && opts.regex.hrs24.test(chrs)) {

                            var tmp = parseInt(chrs, 10);

                            if (tmp == 24) {
                                buffer[pos + 5] = "a";
                                buffer[pos + 6] = "m";
                            } else {
                                buffer[pos + 5] = "p";
                                buffer[pos + 6] = "m";
                            }

                            tmp = tmp - 12;

                            if (tmp < 10) {
                                buffer[pos] = tmp.toString();
                                buffer[pos - 1] = "0";
                            } else {
                                buffer[pos] = tmp.toString().charAt(1);
                                buffer[pos - 1] = tmp.toString().charAt(0);
                            }

                            return { "pos": pos, "c": buffer[pos] };
                        }

                        return isValid;
                    },
                    cardinality: 2,
                    prevalidator: [{
                        validator: function (chrs, buffer, pos, strict, opts) {
                            var isValid = opts.regex.hrspre.test(chrs);
                            if (!strict && !isValid) {
                                isValid = opts.regex.hrs.test("0" + chrs);
                                if (isValid) {
                                    buffer[pos] = "0";
                                    pos++;
                                    return { "pos": pos };
                                }
                            }
                            return isValid;
                        }, cardinality: 1
                    }]
                },
                't': { //am/pm
                    validator: function (chrs, buffer, pos, strict, opts) {
                        return opts.regex.ampm.test(chrs + "m");
                    },
                    casing: "lower",
                    cardinality: 1
                }
            },
            insertMode: false,
            autoUnmask: false
        },
        'datetime12': {
            mask: "1/2/y h:s t\\m",
            placeholder: "dd/mm/yyyy hh:mm xm",
            alias: "datetime",
            hourFormat: "12"
        },
        'hh:mm t': {
            mask: "h:s t\\m",
            placeholder: "hh:mm xm",
            alias: "datetime",
            hourFormat: "12"
        },
        'h:s t': {
            mask: "h:s t\\m",
            placeholder: "hh:mm xm",
            alias: "datetime",
            hourFormat: "12"
        },
        'hh:mm:ss': {
            mask: "h:s:s",
            autoUnmask: false
        },
        'hh:mm': {
            mask: "h:s",
            autoUnmask: false
        },
        'date': {
            alias: "dd/mm/yyyy" // "mm/dd/yyyy"
        }
    });
})(jQuery);
/*
Input Mask plugin extensions
http://github.com/RobinHerbots/jquery.inputmask
Copyright (c) 2010 - 2013 Robin Herbots
Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
Version: 2.3.17

Optional extensions on the jquery.inputmask base
*/
(function ($) {
    //number aliases
    $.extend($.inputmask.defaults.aliases, {
        'decimal': {
            mask: "~",
            placeholder: "",
            repeat: "*",
            greedy: false,
            numericInput: false,
            isNumeric: true,
            digits: "*", //numer of digits
            groupSeparator: "",//",", // | "."
            radixPoint: ".",
            groupSize: 3,
            autoGroup: false,
            allowPlus: true,
            allowMinus: true,
            getMaskLength: function (buffer, greedy, repeat, currentBuffer, opts) { //custom getMaskLength to take the groupSeparator into account
                var calculatedLength = buffer.length;

                if (!greedy) {
                    if (repeat == "*") {
                        calculatedLength = currentBuffer.length + 1;
                    } else if (repeat > 1) {
                        calculatedLength += (buffer.length * (repeat - 1));
                    }
                }

                var escapedGroupSeparator = $.inputmask.escapeRegex.call(this, opts.groupSeparator);
                var escapedRadixPoint = $.inputmask.escapeRegex.call(this, opts.radixPoint);
                var currentBufferStr = currentBuffer.join(''), strippedBufferStr = currentBufferStr.replace(new RegExp(escapedGroupSeparator, "g"), "").replace(new RegExp(escapedRadixPoint), ""),
                groupOffset = currentBufferStr.length - strippedBufferStr.length;
                return calculatedLength + groupOffset;
            },
            postFormat: function (buffer, pos, reformatOnly, opts) {
                if (opts.groupSeparator == "") return pos;
                var cbuf = buffer.slice(),
                    radixPos = $.inArray(opts.radixPoint, buffer);
                if (!reformatOnly) {
                    cbuf.splice(pos, 0, "?"); //set position indicator
                }
                var bufVal = cbuf.join('');
                if (opts.autoGroup || (reformatOnly && bufVal.indexOf(opts.groupSeparator) != -1)) {
                    var escapedGroupSeparator = $.inputmask.escapeRegex.call(this, opts.groupSeparator);
                    bufVal = bufVal.replace(new RegExp(escapedGroupSeparator, "g"), '');
                    var radixSplit = bufVal.split(opts.radixPoint);
                    bufVal = radixSplit[0];
                    var reg = new RegExp('([-\+]?[\\d\?]+)([\\d\?]{' + opts.groupSize + '})');
                    while (reg.test(bufVal)) {
                        bufVal = bufVal.replace(reg, '$1' + opts.groupSeparator + '$2');
                        bufVal = bufVal.replace(opts.groupSeparator + opts.groupSeparator, opts.groupSeparator);
                    }
                    if (radixSplit.length > 1)
                        bufVal += opts.radixPoint + radixSplit[1];
                }
                buffer.length = bufVal.length; //align the length
                for (var i = 0, l = bufVal.length; i < l; i++) {
                    buffer[i] = bufVal.charAt(i);
                }
                var newPos = $.inArray("?", buffer);
                if (!reformatOnly) buffer.splice(newPos, 1);

                return reformatOnly ? pos : newPos;
            },
            regex: {
                number: function (opts) {
                    var escapedGroupSeparator = $.inputmask.escapeRegex.call(this, opts.groupSeparator);
                    var escapedRadixPoint = $.inputmask.escapeRegex.call(this, opts.radixPoint);
                    var digitExpression = isNaN(opts.digits) ? opts.digits : '{0,' + opts.digits + '}';
                    var signedExpression = "[" + (opts.allowPlus ? "\+" : "") + (opts.allowMinus ? "-" : "") + "]?";
                    return new RegExp("^" + signedExpression + "(\\d+|\\d{1," + opts.groupSize + "}((" + escapedGroupSeparator + "\\d{" + opts.groupSize + "})?)+)(" + escapedRadixPoint + "\\d" + digitExpression + ")?$");
                }
            },
            onKeyDown: function (e, buffer, opts) {
                var $input = $(this), input = this;
                if (e.keyCode == opts.keyCode.TAB) {
                    var radixPosition = $.inArray(opts.radixPoint, buffer);
                    if (radixPosition != -1) {
                        var masksets = $input.data('_inputmask')['masksets'];
                        var activeMasksetIndex = $input.data('_inputmask')['activeMasksetIndex'];
                        for (var i = 1; i <= opts.digits && i < opts.getMaskLength(masksets[activeMasksetIndex]["_buffer"], masksets[activeMasksetIndex]["greedy"], masksets[activeMasksetIndex]["repeat"], buffer, opts) ; i++) {
                            if (buffer[radixPosition + i] == undefined) buffer[radixPosition + i] = "0";
                        }
                        input._valueSet(buffer.join(''));
                    }
                } else if (e.keyCode == opts.keyCode.DELETE || e.keyCode == opts.keyCode.BACKSPACE) {
                    opts.postFormat(buffer, 0, true, opts);
                    input._valueSet(buffer.join(''));
                }
            },
            definitions: {
                '~': { //real number
                    validator: function (chrs, buffer, pos, strict, opts) {
                        if (chrs == "") return false;
                        if (!strict && pos <= 1 && buffer[0] === '0' && new RegExp("[\\d-]").test(chrs) && buffer.length == 1) { //handle first char
                            buffer[0] = "";
                            return { "pos": 0 };
                        }

                        var cbuf = strict ? buffer.slice(0, pos) : buffer.slice();

                        cbuf.splice(pos, 0, chrs);
                        var bufferStr = cbuf.join('');

                        //strip groupseparator
                        var escapedGroupSeparator = $.inputmask.escapeRegex.call(this, opts.groupSeparator);
                        bufferStr = bufferStr.replace(new RegExp(escapedGroupSeparator, "g"), '');
                        
                        var isValid = opts.regex.number(opts).test(bufferStr);
                        if (!isValid) {
                            //let's help the regex a bit
                            bufferStr += "0";
                            isValid = opts.regex.number(opts).test(bufferStr);
                            if (!isValid) {
                                //make a valid group
                                var lastGroupSeparator = bufferStr.lastIndexOf(opts.groupSeparator);
                                for (i = bufferStr.length - lastGroupSeparator; i <= 3; i++) {
                                    bufferStr += "0";
                                }

                                isValid = opts.regex.number(opts).test(bufferStr);
                                if (!isValid && !strict) {
                                    if (chrs == opts.radixPoint) {
                                        isValid = opts.regex.number(opts).test("0" + bufferStr + "0");
                                        if (isValid) {
                                            buffer[pos] = "0";
                                            pos++;
                                            return { "pos": pos };
                                        }
                                    }
                                }
                            }
                        }

                        if (isValid != false && !strict && chrs != opts.radixPoint) {
                            var newPos = opts.postFormat(buffer, pos, false, opts);
                            return { "pos": newPos };
                        }

                        return isValid;
                    },
                    cardinality: 1,
                    prevalidator: null
                }
            },
            insertMode: true,
            autoUnmask: false
        },
        'integer': {
            regex: {
                number: function (opts) {
                    var escapedGroupSeparator = $.inputmask.escapeRegex.call(this, opts.groupSeparator);
                    var signedExpression = "[" + (opts.allowPlus ? "\+" : "") + (opts.allowMinus ? "-" : "") + "]?";
                    return new RegExp("^" + signedExpression + "(\\d+|\\d{1," + opts.groupSize + "}((" + escapedGroupSeparator + "\\d{" + opts.groupSize + "})?)+)$");
                }
            },
            alias: "decimal"
        }
    });
})(jQuery);
/*
Input Mask plugin extensions
http://github.com/RobinHerbots/jquery.inputmask
Copyright (c) 2010 - 2013 Robin Herbots
Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
Version: 2.3.17

Regex extensions on the jquery.inputmask base
Allows for using regular expressions as a mask
*/
(function ($) {
    $.extend($.inputmask.defaults.aliases, { // $(selector).inputmask("Regex", { regex: "[0-9]*"}
        'Regex': {
            mask: "r",
            greedy: false,
            repeat: "*",
            regex: null,
            regexTokens: null,
            //Thx to https://github.com/slevithan/regex-colorizer for the tokenizer regex
            tokenizer: /\[\^?]?(?:[^\\\]]+|\\[\S\s]?)*]?|\\(?:0(?:[0-3][0-7]{0,2}|[4-7][0-7]?)?|[1-9][0-9]*|x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4}|c[A-Za-z]|[\S\s]?)|\((?:\?[:=!]?)?|(?:[?*+]|\{[0-9]+(?:,[0-9]*)?\})\??|[^.?*+^${[()|\\]+|./g,
            quantifierFilter: /[0-9]+[^,]/,
            definitions: {
                'r': {
                    validator: function (chrs, buffer, pos, strict, opts) {

                        function analyseRegex() {
                            var currentToken = {
                                "isQuantifier": false,
                                "matches": [],
                                "isGroup": false
                            }, match, m, opengroups = [];

                            opts.regexTokens = [];

                            // The tokenizer regex does most of the tokenization grunt work
                            while (match = opts.tokenizer.exec(opts.regex)) {
                                m = match[0];
                                switch (m.charAt(0)) {
                                    case "[": // Character class
                                    case "\\":  // Escape or backreference
                                        if (currentToken["isGroup"] !== true) {
                                            currentToken = {
                                                "isQuantifier": false,
                                                "matches": [],
                                                "isGroup": false
                                            };
                                            opts.regexTokens.push(currentToken);
                                        }
                                        if (opengroups.length > 0) {
                                            opengroups[opengroups.length - 1]["matches"].push(m);
                                        } else {
                                            currentToken["matches"].push(m);
                                        }
                                        break;
                                    case "(": // Group opening
                                        currentToken = {
                                            "isQuantifier": false,
                                            "matches": [],
                                            "isGroup": true
                                        };
                                        opengroups.push(currentToken);
                                        break;
                                    case ")": // Group closing
                                        var groupToken = opengroups.pop();
                                        if (opengroups.length > 0) {
                                            opengroups[opengroups.length - 1]["matches"].push(groupToken);
                                        } else {
                                            currentToken = groupToken;
                                            opts.regexTokens.push(currentToken);
                                        }
                                        break;
                                    case "{": //Quantifier
                                        var quantifier = {
                                            "isQuantifier": true,
                                            "matches": [m],
                                            "isGroup": false
                                        };
                                        if (opengroups.length > 0) {
                                            opengroups[opengroups.length - 1]["matches"].push(quantifier);
                                        } else {
                                            currentToken["matches"].push(quantifier);
                                        }
                                        break;
                                    default:
                                        // Vertical bar (alternator) 
                                        // ^ or $ anchor
                                        // Dot (.)
                                        // Literal character sequence
                                        if (opengroups.length > 0) {
                                            opengroups[opengroups.length - 1]["matches"].push(m);
                                        } else {
                                            currentToken["matches"].push(m);
                                        }
                                }
                            }
                        };

                        function validateRegexToken(token, fromGroup) {
                            var isvalid = false;
                            if (fromGroup) {
                                regexPart += "(";
                                openGroupCount++;
                            }
                            for (var mndx = 0; mndx < token["matches"].length; mndx++) {
                                var matchToken = token["matches"][mndx];
                                if (matchToken["isGroup"] == true) {
                                    isvalid = validateRegexToken(matchToken, true);
                                } else if (matchToken["isQuantifier"] == true) {
                                    matchToken = matchToken["matches"][0];
                                    var quantifierMax = opts.quantifierFilter.exec(matchToken)[0].replace("}", "");
                                    var testExp = regexPart + "{1," + quantifierMax + "}"; //relax quantifier validation
                                    for (var j = 0; j < openGroupCount; j++) {
                                        testExp += ")";
                                    }
                                    var exp = new RegExp("^" + testExp + "$");
                                    isvalid = exp.test(bufferStr);
                                    regexPart += matchToken;
                                }
                                else {
                                    regexPart += matchToken;
                                    var testExp = regexPart.replace(/\|$/, "");
                                    for (var j = 0; j < openGroupCount; j++) {
                                        testExp += ")";
                                    }
                                    var exp = new RegExp("^" + testExp + "$");
                                    isvalid = exp.test(bufferStr);
                                }
                                if (isvalid) break;
                            }

                            if (fromGroup) {
                                regexPart += ")";
                                openGroupCount--;
                            }

                            return isvalid;
                        }


                        if (opts.regexTokens == null) {
                            analyseRegex();
                        }

                        var cbuffer = buffer.slice(), regexPart = "", isValid = false, openGroupCount = 0;
                        cbuffer.splice(pos, 0, chrs);
                        var bufferStr = cbuffer.join('');
                        for (var i = 0; i < opts.regexTokens.length; i++) {
                            var regexToken = opts.regexTokens[i];
                            isValid = validateRegexToken(regexToken, regexPart, regexToken["isGroup"]);
                            if (isValid) break;
                        }

                        return isValid;
                    },
                    cardinality: 1
                }
            }
        }
    });
})(jQuery);

/*! 
* MD5 js implementation (RFC 1321) - v2.1 
* Copyright (c) 2002 Paul Johnston; Licensed BSD */

/**
* JavaScript implementation of the Message Digest Algorithm, as defined in RFC 1321.
* Version 2.1 Copyright (C) Paul Johnston 1999 - 2002.
* Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
* Distributed under the BSD License 
*/
var hexcase=0,b64pad="",chrsz=8;
function hex_md5(c){return binl2hex(core_md5(str2binl(c),c.length*chrsz))}
function core_md5(c,g){c[g>>5]|=128<<g%32;c[(g+64>>>9<<4)+14]=g;for(var a=1732584193,b=-271733879,d=-1732584194,e=271733878,f=0;f<c.length;f+=16)var h=a,i=b,j=d,k=e,a=md5_ff(a,b,d,e,c[f+0],7,-680876936),e=md5_ff(e,a,b,d,c[f+1],12,-389564586),d=md5_ff(d,e,a,b,c[f+2],17,606105819),b=md5_ff(b,d,e,a,c[f+3],22,-1044525330),a=md5_ff(a,b,d,e,c[f+4],7,-176418897),e=md5_ff(e,a,b,d,c[f+5],12,1200080426),d=md5_ff(d,e,a,b,c[f+6],17,-1473231341),b=md5_ff(b,d,e,a,c[f+7],22,-45705983),a=md5_ff(a,b,d,e,c[f+8],7,1770035416),e=md5_ff(e,a,b,d,c[f+9],12,-1958414417),d=md5_ff(d,e,a,b,c[f+10],17,-42063),b=md5_ff(b,d,e,a,c[f+11],22,-1990404162),a=md5_ff(a,b,d,e,c[f+12],7,1804603682),e=md5_ff(e,a,b,d,c[f+13],12,-40341101),d=md5_ff(d,e,a,b,c[f+14],17,-1502002290),b=md5_ff(b,d,e,a,c[f+15],22,1236535329),a=md5_gg(a,b,d,e,c[f+1],5,-165796510),e=md5_gg(e,a,b,d,c[f+6],9,-1069501632),d=md5_gg(d,e,a,b,c[f+11],14,643717713),b=md5_gg(b,d,e,a,c[f+0],20,-373897302),a=md5_gg(a,b,d,e,c[f+5],5,-701558691),e=md5_gg(e,a,b,d,c[f+10],9,38016083),d=md5_gg(d,e,a,b,c[f+15],14,-660478335),b=md5_gg(b,d,e,a,c[f+4],20,-405537848),a=md5_gg(a,b,d,e,c[f+9],5,568446438),e=md5_gg(e,a,b,d,c[f+14],9,-1019803690),d=md5_gg(d,e,a,b,c[f+3],14,-187363961),b=md5_gg(b,d,e,a,c[f+8],20,1163531501),a=md5_gg(a,b,d,e,c[f+13],5,-1444681467),e=md5_gg(e,a,b,d,c[f+2],9,-51403784),d=md5_gg(d,e,a,b,c[f+7],14,1735328473),b=md5_gg(b,d,e,a,c[f+12],20,-1926607734),a=md5_hh(a,b,d,e,c[f+5],4,-378558),e=md5_hh(e,a,b,d,c[f+8],11,-2022574463),d=md5_hh(d,e,a,b,c[f+11],16,1839030562),b=md5_hh(b,d,e,a,c[f+14],23,-35309556),a=md5_hh(a,b,d,e,c[f+1],4,-1530992060),e=md5_hh(e,a,b,d,c[f+4],11,1272893353),d=md5_hh(d,e,a,b,c[f+7],16,-155497632),b=md5_hh(b,d,e,a,c[f+10],23,-1094730640),a=md5_hh(a,b,d,e,c[f+13],4,681279174),e=md5_hh(e,a,b,d,c[f+0],11,-358537222),d=md5_hh(d,e,a,b,c[f+3],16,-722521979),b=md5_hh(b,d,e,a,c[f+6],23,76029189),a=md5_hh(a,b,d,e,c[f+9],4,-640364487),e=md5_hh(e,a,b,d,c[f+12],11,-421815835),d=md5_hh(d,e,a,b,c[f+15],16,530742520),b=md5_hh(b,d,e,a,c[f+2],23,-995338651),a=md5_ii(a,b,d,e,c[f+0],6,-198630844),e=md5_ii(e,a,b,d,c[f+7],10,1126891415),d=md5_ii(d,e,a,b,c[f+14],15,-1416354905),b=md5_ii(b,d,e,a,c[f+5],21,-57434055),a=md5_ii(a,b,d,e,c[f+12],6,1700485571),e=md5_ii(e,a,b,d,c[f+3],10,-1894986606),d=md5_ii(d,e,a,b,c[f+10],15,-1051523),b=md5_ii(b,d,e,a,c[f+1],21,-2054922799),a=md5_ii(a,b,d,e,c[f+8],6,1873313359),e=md5_ii(e,a,b,d,c[f+15],10,-30611744),d=md5_ii(d,e,a,b,c[f+6],15,-1560198380),b=md5_ii(b,d,e,a,c[f+13],21,1309151649),a=md5_ii(a,b,d,e,c[f+4],6,-145523070),e=md5_ii(e,a,b,d,c[f+11],10,-1120210379),d=md5_ii(d,e,a,b,c[f+2],15,718787259),b=md5_ii(b,d,e,a,c[f+9],21,-343485551),a=safe_add(a,h),b=safe_add(b,i),d=safe_add(d,j),e=safe_add(e,k);return[a,b,d,e]}function md5_cmn(c,g,a,b,d,e){return safe_add(bit_rol(safe_add(safe_add(g,c),safe_add(b,e)),d),a)}function md5_ff(c,g,a,b,d,e,f){return md5_cmn(g&a|~g&b,c,g,d,e,f)}function md5_gg(c,g,a,b,d,e,f){return md5_cmn(g&b|a&~b,c,g,d,e,f)}function md5_hh(c,g,a,b,d,e,f){return md5_cmn(g^a^b,c,g,d,e,f)}function md5_ii(c,g,a,b,d,e,f){return md5_cmn(a^(g|~b),c,g,d,e,f)}function core_hmac_md5(c,g){var a=str2binl(c);16<a.length&&(a=core_md5(a,c.length*chrsz));for(var b=Array(16),d=Array(16),e=0;16>e;e++)b[e]=a[e]^909522486,d[e]=a[e]^1549556828;a=core_md5(b.concat(str2binl(g)),512+g.length*chrsz);return core_md5(d.concat(a),640)}function safe_add(c,g){var a=(c&65535)+(g&65535);return(c>>16)+(g>>16)+(a>>16)<<16|a&65535}function bit_rol(c,g){return c<<g|c>>>32-g}function str2binl(c){for(var g=[],a=(1<<chrsz)-1,b=0;b<c.length*chrsz;b+=chrsz)g[b>>5]|=(c.charCodeAt(b/chrsz)&a)<<b%32;return g}function binl2str(c){for(var g="",a=(1<<chrsz)-1,b=0;b<32*c.length;b+=chrsz)g+=String.fromCharCode(c[b>>5]>>>b%32&a);return g}function binl2hex(c){for(var g=hexcase?"0123456789ABCDEF":"0123456789abcdef",a="",b=0;b<4*c.length;b++)a+=g.charAt(c[b>>2]>>8*(b%4)+4&15)+g.charAt(c[b>>2]>>8*(b%4)&15);return a}function binl2b64(c){for(var g="",a=0;a<4*c.length;a+=3)for(var b=(c[a>>2]>>8*(a%4)&255)<<16|(c[a+1>>2]>>8*((a+1)%4)&255)<<8|c[a+2>>2]>>8*((a+2)%4)&255,d=0;4>d;d++)g=8*a+6*d>32*c.length?g+b64pad:g+"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".charAt(b>>6*(3-d)&63);return g};

/*! 
* qinoa-ui - v1.0.0
* https://github.com/cedricfrancoys/qinoa
* Copyright (c) 2015 Cedric Francoys; Licensed GPLv3 */

/**
* This file extends qinoa with UI related methods
*
*/
(function ($, qinoa) {

    /**
    * Keyboard handler for Qinoa console (dialog shows up on 'ctrl + alt + shift')
    *
    */
    $(document).bind('keydown', function(event) {
        if(event.ctrlKey && event.shiftKey && event.altKey) {
            qinoa.console.show();
        }
    });

    $.extend(true, qinoa, {
        /**
        * UI related configuration
        *
        */
        conf: {
            dialog_width: 700
        },

        /**
        * data buffers
        * associative array class=>fields descriptions
        */
        schemas: [],
        i18n: [],
        views: [],
        fields: [],

        /**
        *  dedicated console
        */
        console:{
                stack: $('<p/>'),
                log: function(msg) {
                    this.stack.append(msg + "<br/>");
                },
                show: function() {
                    if(typeof this.dia == 'undefined') {
                        this.dia = $('<div/>')
                                    .append($('<div/>')
                                        .css({'font-size': '11px', 'height': '200px', 'overflow': 'scroll', 'border': 'solid 1px grey'})
                                        .append(this.stack)
                                    )
                                    .dialog({
                                        modal: false,
                                        title: 'Qinoa console',
                                        width: 700,
                                        height: 'auto'
                                    });
                    }
                    this.dia.dialog('open');
                }
        },
        
        dialog: function(conf) {
            conf = $.extend({
                content:    $('<div/>'),
                modal:        true,
                title:        '',
                width:        qinoa.conf.dialog_width,
                height:        'auto',
                minHeight:    100,
                buttons: [
                    {
                        text: "Ok",
                        click: function() {
                            $( this ).dialog( "destroy" );
                        }
                    }
                ],
                position: {
                    my: "center top",
                    at: "center top",
                    of: window
                }
            }, conf);
            // adjust the vertical position of the dialog
            // we need the actual height of rendered content
            // we temporarily append the content to an offscreen DIV (so we keep all events and objects attached to the the content)
            var $temp = $('<div/>').css({'position': 'absolute', 'left': '-10000px'}).append(conf.content).appendTo($('body'));
            var dialog_height = $temp.height() + 50;
            var window_height = $(window).height();
            // if there is any space left, leave one third of it above the dialog
            if(dialog_height < window_height) {
                conf.position = {
                    my: "center top",
                    at: "center top+"+(window_height-dialog_height)/3,
                    of: window
                };
            }
            // don't destroy the content !
            conf.content.detach();
            $temp.remove();
            var $dia = $('<div/>')
            .attr('title', conf.title)
            .append(conf.content)
            .dialog(conf)
            .on('dialogclose', function( event, ui ) {
                $(window).scrollTop(0);
                $dia.dialog('destroy');
            });
            // if dialog height exceeds window height, return to the top
            if(dialog_height > window_height) $(window).scrollTop(0);
            return $dia;
        },
        
        alert: function(conf) {
            var default_conf = {
// todo : translate
                title: 'Alert',
                message: ''
            };
            (function (conf) {
                qinoa.dialog({
                    content:    $('<div/>').css({'padding': '10px'}).html(conf.message),
                    title:        conf.title,
                    buttons:     [
                                    {
// todo : translate
                                        text: "Close",
                                        click: function() { $( this ).dialog( "destroy" ); }
                                    }
                    ]
                });
            })($.extend(default_conf, conf));
        },
        
        confirm: function(conf) {
            var default_conf = {
// todo : translate
                title:        'Confirm',
                message:    ''
            };
            return (function (conf) {
                var deferred = $.Deferred();
                qinoa.dialog({
                    content:     $('<div/>').css('padding', '10px').html(conf.message),
                    title:         conf.title,
                    buttons:    [
                                    {
// todo : translate
                                        text: "Yes",
                                        click: function() {
                                            $(this).dialog( "destroy" );
                                            deferred.resolve();
                                        }
                                    },
                                    {
// todo : translate
                                        text: "No",
                                        click: function() {
                                            $(this).dialog( "destroy" );
                                            deferred.reject();
                                        }
                                    }

                    ]
                });
                return deferred.promise();
            })($.extend(default_conf, conf));
        },
        
        loader: {
            show: function ($item) {
                $item
//                .css('position', 'relative')
                .prepend(
                    $('<div/>')
                    .addClass('qLoader')
                    .append(
                        $('<div/>').addClass('ui-overlay')
                        .append($('<div/>').addClass('ui-widget-overlay'))
                        .append($('<div/>').addClass('ui-widget-shadow ui-corner-all'))
                    )
                    .append(
                        $('<div/>')
                        .addClass('qinoa-ui-loader ui-corner-all')
                    )
                );
            },
            hide: function ($item) {
                $('.qLoader', $item).remove();
            }
        },

        /**
        *  Retrieves specified fields for objects of selected class and matching the given criteria
        *  This method is a combination of the search and read methods and is useful for lists
        */
        find: function(class_name, fields, domain, order, sort, start, limit, lang) {
                var deferred = $.Deferred();
                $.when(qinoa.search(class_name, domain, order, sort, start, limit, lang))
                .done(function (ids) {
                    $.when(qinoa.read(class_name, ids, fields, lang))
                    .done(function (data) {
                        // build resulting object keeping the order provided by the search method
                        var res = {};
                        $.each(ids, function(i, id) {
                            // make sure id is among the returned fields
                            data[id].id = id;
                            res[i] = data[id];
                        });
                        deferred.resolve(res);
                    })
                    .fail(function (code) {
                        deferred.reject(code);
                    });
                })
                .fail(function () {
                    deferred.reject(qinoa.conf.UNKNOWN_ERROR);
                });
                return deferred.promise();
        },



        /**
        * ObjectManager methods
        */
        getObjectPackageName: function (class_name) {
                return class_name.substr(0, class_name.indexOf('\\'));

        },
        
        getObjectName: function(class_name) {
                return class_name.substr(class_name.indexOf('\\')+1);
        },

        /**
        * schema methods
        returns an associative object mapping each field to its description
        */
        get_schema: function(class_name) {
                var deferred = $.Deferred();
                var package_name = qinoa.getObjectPackageName(class_name);
                var object_name = this.getObjectName(class_name);
                if(typeof qinoa.schemas[package_name] == 'undefined') qinoa.schemas[package_name] = [];
                if(typeof qinoa.schemas[package_name][object_name] == 'undefined') {
                    $.ajax({
                        type: 'GET',
                        url: 'index.php?get=core_objects_schema&class_name='+class_name,
                        async: true,
                        dataType: 'json',
                        contentType: 'application/json; charset=utf-8',
                        success: function(data){
                            var res = false;
                            if(typeof data.result == 'number') qinoa.console.log('Error raised by qinoa.get_schema('+class_name+'): '+qinoa.error_codes[data.result]);
                            else if(typeof data.result == 'object') res = data.result;
                            qinoa.schemas[package_name][object_name] = res;
                            deferred.resolve(res);
                        },
                        error: function(e){
                            // data not found
                            qinoa.schemas[package_name][object_name] = false;
                            deferred.resolve(false);
                        }
                    });
                }
                else deferred.resolve(qinoa.schemas[package_name][object_name]);
                return deferred.promise();
        },


        /**
        * i18n methods
        */
        get_lang: function(class_name, lang) {
                var deferred = $.Deferred();
                var package_name = this.getObjectPackageName(class_name);
                if(typeof(qinoa.i18n[package_name]) == 'undefined') qinoa.i18n[package_name] = [];
                if(typeof(qinoa.i18n[package_name][class_name]) == 'undefined') {
                    $.ajax({
                        type: 'GET',
                        // note : we could try to get directly the file URL ('packages/'+package_name+'/i18n/'+lang+'/'+class_name+'.json')
                        // but sometimes browsers are troubled when a 404 occurs for an ajax request

                        url: 'index.php?get=core_i18n_lang&class_name='+class_name+'&lang='+lang,
                        async: true,
                        dataType: 'json',
                        contentType: 'application/json; charset=utf-8',
                        success: function(data){
                            var res = false;
                            if(typeof data.result == 'number') qinoa.console.log('Error raised by qinoa.get_lang('+class_name+', '+lang+'): '+qinoa.error_codes[data.result]);
                            else if(typeof data.result == 'object') res = data.result;
                            qinoa.i18n[package_name][class_name] = res;
                            deferred.resolve(res);
                        },
                        error: function(e){
                            // data not found
                            qinoa.i18n[package_name][class_name] = false;
                            deferred.resolve(false);
                        }
                    });


                }
                else deferred.resolve(qinoa.i18n[package_name][class_name]);
                return deferred.promise();
        },

        /**
        * views methods
        */
        /*
            Returns html from the related view
        */
        get_view: function(class_name, view_name) {
                var deferred = $.Deferred();
                if(class_name === null || view_name === null) {
                    deferred.reject(qinoa.conf.MISSING_PARAM);
                }
                else {
                    var package_name = qinoa.getObjectPackageName(class_name);
                    var object_name     = qinoa.getObjectName(class_name);
                    if(typeof qinoa.views[package_name] == 'undefined') qinoa.views[package_name] = [];
                    if(typeof qinoa.views[package_name][object_name] == 'undefined') qinoa.views[package_name][object_name] = [];
                    if(typeof qinoa.views[package_name][object_name][view_name] == 'undefined') {
                        $.ajax({
                            type: 'GET',
                            // note : we could try to get directly the file URL (''packages/'+package_name+'/views/'+object_name+'.'+view_name+'.html')
                            // but browsers don't always behave nicely when a 404 occurs for an ajax request
                            url: 'index.php?get=core_objects_view&class_name='+class_name+'&view_name='+view_name,
                            async: true,
                            dataType: 'json',
                            contentType: 'application/json; charset=utf-8'
                        })
                        .done(function (data) {
                            try {
                                if(typeof data != 'object')           throw Error(qinoa.conf.UNKNOWN_ERROR);
                                if(typeof data.result == 'number')    throw Error(data.result);
                                if(typeof data.result != 'string')    throw Error(qinoa.conf.UNKNOWN_OBJECT);
                                qinoa.views[package_name][object_name][view_name] = data.result;
                                deferred.resolve(data.result);
                            }
                            catch(e) { deferred.reject(e.message); }
                        })
                        .fail(function () {
                            // if an error occurs, we set value to false to prevent further requests
                            qinoa.views[package_name][object_name][view_name] = false;
                            deferred.reject(qinoa.conf.UNKNOWN_ERROR);
                        });
                    }
                    else deferred.resolve(qinoa.views[package_name][object_name][view_name]);
                }
                return deferred.promise();
        },
        
        /*
            Returns an associatie object mappging each field present in the view to its attributes
        */
        get_fields: function(class_name, view_name) {
                var deferred = $.Deferred();
                if(class_name === null || view_name === null) {
                    deferred.reject(qinoa.conf.MISSING_PARAM);
                }
                else {
                    var package_name = this.getObjectPackageName(class_name);
                    var object_name = this.getObjectName(class_name);
                    if(typeof qinoa.fields[package_name] == 'undefined') qinoa.fields[package_name] = [];
                    if(typeof qinoa.fields[package_name][object_name] == 'undefined') qinoa.fields[package_name][object_name] = [];
                    if(typeof qinoa.fields[package_name][object_name][view_name] == 'undefined') {
                        qinoa.fields[package_name][object_name][view_name] = {};
                        var item_type;
                        switch(view_name.split('.')[0]) {
                            case 'form' :
                                item_type = 'var';
                                break;
                            case 'report' :
                            case 'list' :
                                item_type = 'li';
                                break;
                        }
                        $.when(qinoa.get_view(class_name, view_name))
                        .done(function(result) {
                            // returned view might be set to false
                            if(result) {
                                var $q = $({}); 
                                $('<div/>')
                                .append(result)
                                .find(item_type)
                                .each(function() {
                                    var item = this;
                                    var field = $(this).attr('id');                                
                                    $q.queue(function (next) {                                        
                                        var attributes = {};
                                        $.each(item.attributes, function(i, attr) {
                                            attributes[attr.name] = attr.value;
                                        });
                                        qinoa.fields[package_name][object_name][view_name][field] = attributes;
                                        // handle dot notation: if detected, we have to recurse throught classes to find out target type
                                        var parts = field.split('.');
                                        if(parts.length > 1) {
                                            var $q1 = $({});
                                            $q1.sub_class_name = class_name; 
                                            $q1.sub_package_name = package_name;
                                            $q1.sub_object_name = object_name;                                  
                                            $.each(parts, function(i, part) {
                                                $q1.queue( function (next1) { 
                                                    $.when(qinoa.get_schema($q1.sub_class_name))
                                                    .done(function (result) {
                                                        var type = qinoa.schemas[$q1.sub_package_name][$q1.sub_object_name][part]['type'];
                                                        qinoa.schemas[package_name][object_name][field] = {};
                                                        qinoa.schemas[package_name][object_name][field]['type'] = type;                                          
                                                        
                                                        if(typeof qinoa.schemas[$q1.sub_package_name][$q1.sub_object_name][part]['foreign_object'] != 'undefined') {
                                                            $q1.sub_class_name = qinoa.schemas[$q1.sub_package_name][$q1.sub_object_name][part]['foreign_object'];
                                                            $q1.sub_package_name = qinoa.getObjectPackageName($q1.sub_class_name);
                                                            $q1.sub_object_name = qinoa.getObjectName($q1.sub_class_name);
                                                        }                                                        
                                                        next1(); 
                                                    })
                                                    .fail(function (code) {
                                                        next1(); 
                                                    });
                                                });
                                            });
                                            $q1.queue(function (next1) {
                                                next();
                                            });
                                        }                                     
                                        else next();
                                    });
                                });
                                $q.queue(function (next) {
                                    deferred.resolve(qinoa.fields[package_name][object_name][view_name]); 
                                });
                            }
                            else deferred.reject(qinoa.conf.UNKNOWN_ERROR);
                        })
                        .fail(function (code) {
                            qinoa.console.log('Error in qinoa-ui raised by qinoa.get_view('+class_name+','+view_name+'): '+qinoa.error_codes[code]);
                        });
                    }
                    else deferred.resolve(qinoa.fields[package_name][object_name][view_name]);
                }
                return deferred.promise();
        },

    });
})(jQuery, qinoa);



/**

qinoa UI usage examples

$.when(qinoa.confirm({title: 'test', message: 'confirmation'}))
.done(function() {
    console.log('yes');
})
.fail(function() {
    console.log('no');
});



*/

(function($){
/* This object holds the methods for rendering qGrid cells
*  and can be extended to handle additional types
*
*/
qinoa.GridCells = {
	'string': function ($this, conf) {
		var $widget;
		if(conf.mode == 'view') {
			$widget = $('<span/>').text(conf.value[conf.id]);
		}
		else if(conf.mode == 'edit') {
			$widget = $('<input type="text"/>')
			// 'name' attribute will generate data at form submission
			.attr({name: conf.name})
			.uniqueId()
			.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
			// assign the specified value
			.val(conf.value[conf.id])
			// define the expected .data('value') method
			.data('value', function() {return $(this).val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');		
		}
		return $widget;
	},
	'boolean': function ($this, conf) {
		return qinoa.GridCells.string($this, conf);
	},    
	'integer': function ($this, conf) {
		return qinoa.GridCells.string($this, conf);
	},
	'float': function ($this, conf) {
		return qinoa.GridCells.string($this, conf);
	},	
	'date': function ($this, conf) {
		// force format conversion<
		// note : we should have receive the date in the right format but widget attribute might override the datetime type
		var value = $.datepicker.formatDate( qinoa.conf.QN_DATE_FORMAT, Date.parse(conf.value[conf.id]) );
		var $widget;
		if(conf.mode == 'view') {
			$widget = $('<span/>').text(value);
		}
		else if(conf.mode == 'edit') {
			$widget = $('<input type="text"/>')
			// 'name' attribute will generate data at form submission
			.attr({name: conf.name})
			.uniqueId()
			.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
			// assign the specified value
			.val(conf.value[conf.id])
			// define the expected .data('value') method
			.data('value', function() {return $(this).val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');		
		}
		return $widget;	
		
	},
	'datetime': function ($this, conf) {
		// force format conversion
		// var value = Date.parse(conf.value[conf.id], qinoa.conf.QN_DATETIME_FORMAT );
		var value = conf.value[conf.id];
		var $widget;		
		if(conf.mode == 'view') {
			$widget = $('<span/>').text(value);
		}
		else if(conf.mode == 'edit') {
			$widget = $('<input type="text"/>')
			// 'name' attribute will generate data at form submission
			.attr({name: conf.name})
			.uniqueId()
			.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
			// assign the specified value
			.val(conf.value[conf.id])
			// define the expected .data('value') method
			.data('value', function() {return $(this).val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');		
		}
		return $widget;	
	},
	'many2one': function ($this, conf) {
		var $widget = $('<a/>')
		.addClass('ui-state-default')
		.attr('href', '#');
		$.each(conf.fields, function (i, field) {
            $widget.append( (i > 0)? ', '+conf.value[field] : conf.value[field] );
		});
		$widget.on('click', function() {
// todo : temporary (might change after edition)
			var object_id = conf.value[conf.id];
// todo : handle views attribute inside LI 			
			var view = 'form.default';
			var $form = $('<form/>')
			.qForm($.extend(true, conf, {
				view: view,
				object_id: object_id,
				predefined: {
					class_name: conf.class_name,
					ids: object_id,
					lang: conf.lang
				}
			}))
			.on('ready', function() {
				$dia = qinoa.dialog({
					content:	$form,
					title:		'Object edition: '+conf.class_name+' ('+object_id+') - '+view,
					buttons:	[]
				})
				.on('formclose', function(event, action) {
					if(typeof action != 'undefined' && action != 'cancel') {
						$this.closest('.ui-grid').trigger('reload');
					}
					$dia.trigger('dialogclose');
				});
				
			});
			
		});
		return $widget;
	}
};

$.fn.qGridCell = function(conf){
	var default_conf = {
		mode:	'view',
		type:	'string'
	}
	return this.each(function() {
		return (function ($this, conf) {
			try {
				if(typeof qinoa.GridCells[conf.type] == 'undefined') throw Error('Error raised in qinoa-ui.qGridCell : unknown type '+conf.type);
				var $widget = qinoa.GridCells[conf.type]($this, conf);
				return $this.data('widget', $widget.appendTo($this));
			}
			catch(e) {
				qinoa.console.log(conf.type+' '+conf.name+e.message);
			}
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery);
(function($, qinoa){

$.fn.qGrid = function(conf){

	var default_conf = {
	// internal params (
		columns:		[],									// holds, for each filed its name, display name and width
		fields:			[],									// fields of the specified class appearing in the view (used in 'feed' method)
		more:			[],									// ids to include to the domain
		less:			[],									// ids to exclude from the domain
		selection:		{},									// list of selected items (support seleciton upon several pages)
	// mandatory params
		class_name:		'',									// class of the objects to list
		view:			'list.default',						// name of the view to use
	// optional params
		multiple:		true,
		rp: 			20,									// number of results per page
		rp_choices:		[5, 10, 20, 40, 80, 160],			// allowed per-page values
		page:			1,									// default page to display
		total:			0,									// total number of pages (records/rp)
		records:		0,									// number of records matching the domain
		sortname:		'id',								// default field on which perform sort
		sortorder:		'asc',								// order for sorting
		domain:			[[]],								// domain (i.e. clauses to limit the results)
		lang:			qinoa.conf.content_lang,			// language in which request the content to server
		ui:				qinoa.conf.user_lang,				// language in which display UI items
		views :		{
					edit: 'form.default',
					add:  'list.default'
					},
		buttons:	{
					edit:	{
							text: 'edit',						// alternate text for the edit button
							icon: 'fa fa-pencil'				// icon for the edition button
							},
					del:
							{
							text: 'delete',						// alternate text for the delete button
							icon: 'fa fa-trash'				// icon for the delete button
							},
					add:
							{
							text: 'create new',					// alternate text for the add button
							icon: 'fa fa-file-o'			// icon for the add button
							}
					},
		actions:	{}											// functions related to buttons (default defined in listen method)
	};

	/**
	* Retrieves the html source of the requested view
	*
	*/
	load_view = function($grid, conf) {
		var deferred = $.Deferred();
		// init internal (we might have received values from conf of a parent widget)
		conf.columns	= [];
		conf.fields		= [];
		conf.more		= [];
		conf.less		= [];
		conf.selection	= {};
		
		var schema, fields;
		var $view;
		$({})
		.queue( function (next) { 
			$.when(qinoa.get_schema(conf.class_name))
			.done(function (result)	{ schema = result; next();})
			.fail(function (code)	{ deferred.reject(code); });
		})
		.queue(	function (next) { 
			$.when(qinoa.get_view(conf.class_name, conf.view))
			.done(function (result)	{ $view = $(result); next();})
			.fail(function (code)	{ deferred.reject(code); });
		})
		.queue( function (next) {
			$.when(qinoa.get_fields(conf.class_name, conf.view))
			.done(function (result)	{ fields = result; next();})
			.fail(function (code)	{ deferred.reject(code); });
		})
		.queue( function (next) {
			// extend the configuration object with view attributes, if any
			// note: attributes we should expect are: 'domain', 'sortname', 'sortorder'
			// note: this means that view attributes overwrites any conf-defined attributes
			$.each($view[0].attributes, function(i, attr) {
				switch(attr.name) {
				case 'domain':
					// check syntax validity
					try {
						conf.domain = JSON.parse(attr.value.replace(/\'/g, '"'));
					} catch (e) {
						qinoa.console.log("Error raised in qinoa-ui.qGrid::load_view ("+conf.view+"): attribute 'domain' has invalid syntax");
					}
					break;
				case 'views':
					try {
						conf.views = JSON.parse(attr.value.replace(/\'/g, '"'));
					} catch(e) {
						qinoa.console.log("Error raised in qinoa-ui.qGrid::load_view ("+conf.view+"): attribute 'views' has invalid syntax");
					}
					break;
				default:
					conf[attr.name] = attr.value;
					break;
				}
			});
			var fieldsQueue = $({});
			// extract the fields from the view and generate the columns model			
			$.each(fields, function (field, attributes) {
				fieldsQueue.queue( function (next) {
					// build the columns and fields arrays
					var name = field;
					var width = attributes['width'];
					if(parseInt(width) > 0) {
						var column = $.extend({
							name: name,
							fields:	[]				// array holding names of fields whose value is required by widgets related to the column
						}, attributes);
						column.fields.push(field);
						conf.fields.push(field);						
						if(typeof schema[field]['foreign_object'] != 'undefined' && attributes['view'] != undefined) {
							// load specified view and add related fields
							$.when(qinoa.get_fields(schema[field]['foreign_object'], attributes['view']))
							.done(function (result) {
								$.each(result, function (field, attributes) {
									var related = name+'.'+field;
									conf.fields.push(related);
									column.fields.push(related);
								});
								conf.columns.push(column);
								next();
							})
							.fail(function (code) { 
								// deferred.reject(code); 
								console.log('failed');
							});
						}
						else {
							conf.columns.push(column);
							next();
						}
					}
				});
			});
			fieldsQueue.queue(function (next) {
				deferred.resolve();
			});
		});
		return deferred.promise();
	};

	render = function($grid, conf) {
		var deferred = $.Deferred();
		// create table
		var $table = $('<table/>').addClass('grid_table ui-widget-content');
		var $thead = $('<thead/>').addClass('grid_table_head ui-widget-content');
		var $tbody = $('<tbody/>').addClass('grid_table_body ui-widget-content');

		// instanciate header row
		var $hrow = $('<tr/>').addClass('grid_table_head_row');

		// create the first column, containing the 'select-all' checkbox
		var $cell = $('<th/>')
		.addClass('ui-state-default')
		.css({'width': '30px'})
		.append(
			$('<div/>').css({'width': '30px'})
			.append(
				$('<input type="checkbox" />')
				.addClass('checkbox')
				.css({'width': '20px'})
				// click triggers de/select all
				.on('click', function() {
					var checked = this.checked;
					$("input:checkbox", $tbody).each(function(i, elem) {
						var $parent = $(this).parents('tr.grid_table_body_row').first();
						var id = $parent.attr('id');
						if(checked) {
							$parent.addClass('ui-state-active');
							elem.checked = true;
							conf.selection[id] = true;
						}
						else {
							$parent.removeClass('ui-state-active');
							elem.checked = false;
							delete conf.selection[id];
						}

					});
				})
			)
		).appendTo($hrow);

		if(!conf.multiple) $('input', $cell).attr('disabled', 'disabled');

		// create other columns, based on the columns given in the configuration
		$.each(conf.columns, function(i, column) {
			$cell = $('<th/>').attr('name', column.id)
			.addClass('ui-state-default')
			.css({'width': column.width, 'text-align': 'left'})
			.append($('<div/>').append($('<label/>').attr('for', column.id)))
			.append($('<span/>').addClass('ui-icon'))
			.hover(
				/** The div style attr 'asc' or 'desc' is for the display of the arrow
				  * the th style attr 'asc' or 'desc' is to memorize the current order
				  * so, when present, both attributes should always be inverted
				  */
				function() {
					// set hover
					$this = $(this).addClass('ui-state-hover');
					$div = $('div', $this);
					$span = $('span', $this);
					if($('.sorted', $thead).attr('name') == $this.attr('name') && conf.sortorder == 'asc') {
						$div.removeClass('asc').addClass('desc');
						$span.removeClass('ui-icon-triangle-1-n').addClass('ui-icon-triangle-1-s');
					}
					else {
						$div.removeClass('desc').addClass('asc');
						$span.removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-n');
					}
				},
				function() {
					// unset hover
					$this = $(this).removeClass('ui-state-hover');
					$div = $('div', $this);
					$span = $('span', $this);
					$div.removeClass('asc').removeClass('desc');
					$span.removeClass('ui-icon-triangle-1-n').removeClass('ui-icon-triangle-1-s');
					if($('.sorted', $thead).attr('name') == $this.attr('name')) {
						if($this.hasClass('asc')) {
							$div.addClass('asc');
							$span.addClass('ui-icon-triangle-1-n');
						}
						else {
							$div.addClass('desc');
							$span.addClass('ui-icon-triangle-1-s');
						}
					}
			})
			.on('click', function() {
					// change sortname and/or sortorder
					$this = $(this);
					$sorted = $('.sorted', $thead);
					$div = $('div', $this);
					$span = $('span', $this);
					if($sorted.attr('name') == $this.attr('name')) {
						if($div.hasClass('asc')) {
							$div.removeClass('asc').addClass('desc');
							$span.removeClass('ui-icon-triangle-1-n').addClass('ui-icon-triangle-1-s');
							$this.removeClass('desc').addClass('asc');
							conf.sortorder = 'asc';
						}
						else {
							$div.removeClass('desc').addClass('asc');
							$span.removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-n');
							$this.removeClass('asc').addClass('desc');
							conf.sortorder = 'desc';
						}
					}
					else {
						$this.addClass('sorted').addClass('asc');
						$div.removeClass('asc').addClass('desc');
						$span.removeClass('ui-icon-triangle-1-n').addClass('ui-icon-triangle-1-s');
						$sorted.removeClass('sorted asc desc');
						$('div', $sorted).removeClass('asc desc');
						$('span', $sorted).removeClass('ui-icon-triangle-1-n ui-icon-triangle-1-s');
						conf.sortorder = 'asc';
					}
					conf.sortname = $this.attr('name');
					// uncheck selection box
					$("input:checkbox", $thead)[0].checked = false;
					// refresh list
					feed($grid, conf);
				}
			);
			if(column.id == conf.sortname) {
				$cell.addClass('sorted').addClass(conf.sortorder);
				$('div', $cell).addClass(conf.sortorder);
			}
			$hrow.append($cell);
		});

		$grid.addClass('ui-grid ui-front ui-widget ui-widget-content ui-corner-all').append($table.append($thead.append($hrow)).append($tbody));
		deferred.resolve();
		return deferred.promise();
	};



	/**
	* translate terms of the form
	* into the lang specified in the configuration object
	*/
	translate = function($grid, conf) {
		var deferred = $.Deferred();
		var schema, lang;
		$({})
		.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}) })
		.queue(	function (next) { $.when(qinoa.get_lang(conf.class_name, conf.ui)).done(function (result) {lang = result; next(); }) })
		.queue( function (next) {
			if(typeof lang != 'object' || $.isEmptyObject(lang)) {
				// 1) stand-alone labels, legends, buttons (refering to the current view)
				$('label[name],legend[name],button[name]', $grid).each(function() {
					$(this).text(ucfirst($(this).attr('name')));
				});
				// 2) field labels
				$('label[for]', $grid).each(function() {
					$(this).text(ucfirst($(this).attr('for')));
				});
			}
			else {
				// 1) stand-alone labels, legends, buttons (refering to the current view)
				$('label[name],legend[name],button[name]', $grid).each(function() {
					var name = $(this).attr('name');
					if(typeof name != 'undefined') {
						if(typeof lang['view'][name] != 'undefined') {
							var value = lang['view'][name]['label'];
							$(this).text(value);
						}
						else $(this).text(ucfirst(name));
					}
				});
				// 2) field labels
				$('label[for]', $grid).each(function() {
					var value;
					var field = $(this).attr('for');
					if(field != undefined) {
						if(typeof lang['model'][field] != 'undefined' && typeof lang['model'][field]['label'] != 'undefined') {
							$(this).text(lang['model'][field]['label']);
							if(typeof lang['model'][field]['help'] != 'undefined') {
								$(this).append(
									$('<sup/>')
									.attr('title', lang['model'][field]['help'].replace(/\n/g,'<br />'))
									.addClass('help').text('?').tooltip()
								);
							}
						}
						else $(this).text(ucfirst(field));
					}
				});
			}
			deferred.resolve();			
		});
		return deferred.promise();
	};

	init = function($grid, conf) {
		var deferred = $.Deferred();
		$.when(qinoa.search(conf.class_name, conf.domain, conf.sortname, conf.sortorder, 0, 0, conf.lang))
		.done(function(ids) {
			conf.records = Object.keys(ids).length;
			conf.total = Math.ceil(conf.records/conf.rp);
			$.when(feed($grid, conf))
			.done(function() { deferred.resolve(); })
			.fail(function (code) { console.log('feed failed'); });
		})
		.fail(function (code) {
			qinoa.console.log('Error raised in qinoa-ui.qGrid::init by qinoa.search(): '+qinoa.error_codes[code]);
            deferred.reject(code);
		});
		return deferred.promise();
	};

	feed = function($grid, conf) {
		var deferred = $.Deferred();
		// get body and display the loader
		$tbody = $('.grid_table_body', $grid);
		qinoa.loader.show($grid);
		// create a temporary domain with the config domain and, if necessary, do some changes to it
		var domain = $.extend(true, [], conf.domain);
		// add an inclusive OR clause
		if(conf.more.length) domain.push([['id','in', conf.more]]);
		// add an exclusive AND clause
		if(conf.less.length) domain[0].push(['id','not in', conf.less]);

		var start = (conf.page-1) * conf.rp;

		var schema;
		$({})
		.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}) })
		.queue(	function (next) {  
			$.when(qinoa.find(conf.class_name, conf.fields, domain, conf.sortname, conf.sortorder, start, conf.rp, conf.lang))
			.done(function (data) {
				// remove previous content
				$tbody.empty();

				$.each(data, function(i, values) {
					// make sure id is numeric
					var id = values.id;
					$row =
					$('<tr/>')
					.addClass('grid_table_body_row ui-state-default')
					.attr('id', id)
					.append(
						$('<td/>')
						.addClass('grid_table_body_row_checkbox')
						.append(
							$('<input type="checkbox" />')
							.addClass('checkbox')
							.css({'width': '20px'})
	//						.on('dblclick', function() {conf.edit.func($grid, id);})
							.on('click', function () {
								var $parent = $(this).parents('tr.grid_table_body_row').first();
								var id = $parent.attr('id');
								if(this.checked) {
									if(!conf.multiple) {
										// remove previous selection, if any
										$('.grid_table_body_row_checkbox > input', $grid).prop('checked', false );
										$('.grid_table_body_row', $grid).removeClass('ui-state-active');
										conf.selection = {};
									}
									this.checked = true;
									conf.selection[id] = true;
								}
								else delete conf.selection[id];
								$parent.toggleClass('ui-state-active');
							})
						)
					)
					.toggleClass('erow', i%2 == 1);
					$.each(conf.columns, function(j, column) {
//						$row.append($('<td/>').text(values[column.id]));

						var value = {};
						// export fields required by the widget
						$.each(column.fields, function (i, field) {
							value[field] = values[field];
						});					
						var type = (schema[column.id]['type'] == 'function')?schema[column.id]['result_type']:schema[column.id]['type'];
						var cell_conf = $.extend({
							mode:	'view',
							type:	(typeof column.widget != 'undefined')?column.widget:type,
							value:	value,
							lang:	conf.lang
						}, column);	
						if(cell_conf.widget != undefined) cell_conf.type = cell_conf.widget;
						if(typeof schema[column.id]['foreign_object'] != 'undefined') cell_conf.class_name = schema[column.id]['foreign_object'];

//						console.log(cell_conf);
				
						$row.append($('<td/>').append(
						$('<div/>').qGridCell(cell_conf)					
						));

					});
					// select items present in current selection
					if(typeof conf.selection[id] != 'undefined') {
						$('.checkbox', $row).prop('checked', true);
						$row.toggleClass('ui-state-focus');
					}
					$tbody.append($row);
				});

				// add pager at the top and bottom of the grid
				$('.ui-grid-pager', $grid).remove();
				$('.ui-grid-footer', $grid).remove();


				$grid.prepend(pager($grid, conf).addClass('ui-corner-top'));
				$grid.append(pager($grid, conf));
				$grid.append(footer($grid, conf).addClass('ui-corner-bottom'));

				qinoa.loader.hide($grid);
				deferred.resolve();
			})
			.fail(function (code) {
				qinoa.console.log('Error raised in qinoa-ui.qGrid::feed by qinoa.find(): '+qinoa.error_codes[code]);
			});
		});		
		return deferred.promise();
	};


	pager = function($grid, conf) {
		var $separator = $('<span/>').addClass('separator').text(' | ');

		
		var $buttons = $('<div/>').css({'left': '5px'});
		$.each(conf.buttons, function (id, button) {
			if(!$.isEmptyObject(button)) {
				$('<span/>')
				.attr('title', button.text)
				.button({icons:{primary:button.icon}, text: false})
				.on('click', function() {
					if(typeof conf.actions[id] == 'function') conf.actions[id]($grid, conf);
				})
				.appendTo($buttons);
			}
		});


		var $results =
		$('<div/>')
		.addClass('ui-front')
		.css('right', '10px')
		// current view info
		.append(
			$('<span/>')
			.append(function(index, html) {
				var start = (conf.page-1) * conf.rp;
				if(start > 0) start++;
				var end = Math.min(start + parseInt(conf.rp) - 1, conf.records);
				return 'Results ' + start + ' - ' + end + ' of ' + conf.records;
			})
		)
		// separator
		.append($separator.clone())
		.append($('<span/>').html('Show&nbsp;&nbsp;'));
		// number of results selection box
		$select = $('<select/>')
		.css('width', '45px');
		$.each(conf.rp_choices, function(i, val) {
			$option = $('<option/>').attr({'value':val}).html(val);
			if(conf.rp == val) {
				$option.prop('selected', true);
			}
			$option.appendTo($select);
		});								
		$select.appendTo($results).selectmenu({
			change: function( event, ui ) {
				conf.rp = this.value;
				conf.page = 1;
				conf.total = Math.ceil(conf.records/conf.rp);
				$grid.trigger('reload');					
			}
		});

		
		var $navigator = 
		$('<div/>')
		.css({'left': '50%', 'margin-left': '-190px'})
		// first page button
		.append(
			$('<span/>')
			.attr('title', 'first')
			.button({icons:{primary:'ui-icon-seek-start'}, text: false})
			.on('click', function() {
				var first = 1;
				if(conf.page != first) {
					conf.page = first;
					$grid.trigger('reload');
				}
			})
		)
		// previous page button
		.append(
			$('<span/>')
			.attr('title', 'prev')
			.button({icons:{primary:'ui-icon-seek-prev'}, text: false})
			.on('click', function() {
				var previous = Math.max(parseInt(conf.page)-1, 1);
				if(conf.page != previous) {
					conf.page = previous;
					$grid.trigger('reload');
				}
			})
		)
		// separator
		.append($separator.clone())
		// current page among total number of result pages
		.append($('<span/>').append('Page ' + conf.page + ' of '+ conf.total))
		// separator
		.append($separator.clone())
		// next page button
		.append(
			$('<span/>')
			.attr('title', 'next')
			.button({icons:{primary:'ui-icon-seek-next'}, text: false})
			.on('click', function() {
				var next = Math.min(parseInt(conf.page)+1, conf.total);
				if(conf.page != next) {
					conf.page = next;
					$grid.trigger('reload');
				}
			})
		)
		// last page button
		.append(
			$('<span/>')
			.attr('title', 'last')
			.button({icons:{primary:'ui-icon-seek-end'}, text: false})
			.on('click', function() {
				var last = conf.total
				if(conf.page != last) {
					conf.page = last;
					$grid.trigger('reload');
				}
			})
		);


		// create pager
		return $('<div/>')
		.addClass('ui-grid-pager ui-widget-header ui-front')
		// 1) action buttons
		.append($buttons)
		// 2) results & info
		.append($results)
		// 3) page navigator
		.append($navigator);
	};

	footer = function($grid, conf) {
		var params = {
			show: 'core_objects_view',
			view: conf.view_name,
			object_class: conf.class_name,
			domain: conf.domain,
			rp: conf.rp,
			page: conf.page,
			sortname: conf.sortname,
			sortorder: conf.sortorder,
			fields: conf.fields
		};

		// create extra widgets at the bottom of the grid
		return $('<div/>').addClass('ui-grid-footer ui-state-default')
			.append($('<div/>').css('margin-left',  '7px')
				.append($('<span/>').text('Export:'))
				.append($('<a/>').css({'margin': '0px 5px'}).attr('href', '?index.php&'+$.param($.extend(params, {output: 'pdf'}))).attr('target', '_blank').append('pdf'))
				.append($('<span/>').text('|'))
				.append($('<a/>').css({'margin': '0px 5px'}).attr('href', '?index.php&'+$.param($.extend(params, {output: 'xls'}))).attr('target', '_blank').append('xls'))
				.append($('<span/>').text('|'))
				.append($('<a/>').css({'margin': '0px 5px'}).attr('href', '?index.php&'+$.param($.extend(params, {output: 'csv'}))).attr('target', '_blank').append('csv'))
			);
	};

	listen = function ($grid, conf) {
		// if actions are not defined yet, we do it now
		// note: these are the actions for default buttons (edit, add, delete)

		if(typeof conf.actions.edit == 'undefined') {
			conf.actions.edit = function ($grid, conf) {
				if(Object.keys(conf.selection).length <= 0) {
					qinoa.alert({
						message:	"<p><b>No item selected.</b></p>"+
									"<p>To edit an object, click its checkbox prior to the 'edit' button.</p>"
					});
					return false;
				}
				var object_id = Object.keys(conf.selection)[0];
/*
				var $form = $('<form/>')
				.qForm($.extend(true, conf, {
					view: conf.views.edit,
					object_id: object_id,
					predefined: {
						class_name: conf.class_name,
						ids: object_id,
						lang: conf.lang
					}
				}))
*/
				var $form = qinoa.form($.extend(true, conf, {
					view: conf.views.edit,
					object_id: object_id,
					predefined: {
						class_name: conf.class_name,
						ids: object_id,
						lang: conf.lang
					}
				}))
				.on('ready', function() {
					$dia = qinoa.dialog({
						content:	$form,
						title:		'Object edition: '+conf.class_name+' ('+object_id+') - '+conf.views.edit,
						buttons:	[]
					})
					.on('formclose', function(event, action) {
						if(typeof action != 'undefined' && action != 'cancel') {
							$grid.trigger('reload');
						}
						$dia.trigger('dialogclose');
					});
				});
			};
		}

		if(typeof conf.actions.del == 'undefined') {
			conf.actions.del = function ($grid, conf) {
				var ids = Object.keys(conf.selection);
				if(ids.length <= 0) {
					qinoa.alert({
						message:	"<p><b>No item selected.</b></p>"+
									"<p>To delete an object, click its checkbox prior to the 'delete' button.</p>"
					});
					return false;
				}
				$.when(qinoa.confirm({
					message:		'<p><b>'+ ids.length +' items selected.</b></p>'+
									'Do you confirm deletion for selected item(s) ?',
					title:			'Deletion'
				}))
				.done(function() {
					$.when(qinoa.remove(conf.class_name, ids, false))
					.done(function() {
						$grid.trigger('reload');
					});
				});
			};
		}
		if(typeof conf.actions.add == 'undefined') {
			conf.actions.add = function ($grid, conf) {
                // request a new object
				var object_id = 0;

                $.when(qinoa.create(conf.class_name, {}, conf.lang))
                .done(function(result) {
                    object_id = result;
                   // instanciate form
                    var $form = qinoa.form($.extend(true, conf, {
                        view: conf.views.edit,
                        object_id: object_id,
                        predefined: {
                            class_name: conf.class_name,
                            ids: object_id,
                            lang: conf.lang
                        }
                    }))
                    .on('ready', function() {
                        $dia = qinoa.dialog({
                            content:	$form,
                            title:		'New object: '+conf.class_name+' - '+conf.views.edit,
                            buttons:	[]
                        })
                        .on('formclose', function(event, action) {
                            if(typeof action != 'undefined' && action != 'cancel') {
                                $grid.trigger('reload');
                            }
                            $dia.trigger('dialogclose');
                        });
                    });                    
                })
                .fail(function(result){
                    qinoa.console.log('Error raised by qinoa.create(): '+qinoa.error_codes[result]);
                });             

			};
		}
	};


	return this.each(function() {
		return (function ($this, conf) {
			$this.hide();

			$.when(load_view($this, conf))
			.then(function () { return render($this, conf); })
			.then(function () { return translate($this, conf); })
			.then(function () { return init($this, conf); })
			.then(function () { listen($this, conf); $this.trigger('ready'); })

			return $this
			.on('reload', function () {
				feed($this, conf);
			})
			.on('ready', function() {
				// we leave an access to internal params (domain, selection, ...)
				$this.data('conf', conf);
				$this.show();
			});

		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery, qinoa);
// require jquery-1.7.1.js (or later), ckeditor.js, jquery-ui.timepicker.js, easyObject.grid.js
// jquery.inputmask.js

// accepted types are: boolean, float, integer, string, short_text, text, date, time, datetime, timestamp, selection, binary, one2many, many2one, many2many
// and some additional types : password, image, code

(function($, qinoa){

/* This object holds the methods for rendering qForm widgets
*  and can be extended to handle additional widgets
*
* defines a .data('value') method
* triggers a 'ready' event when ready
*/
qinoa.FormWidgets = {
	'string': function ($this, conf) {
		var $widget = $('<input type="text"/>')
		// 'name' attribute will generate data at form submission
		.attr({id: conf.id, name: conf.name})
		// set layout and use jquery-UI css
		.addClass('ui-widget')
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		// assign the specified value
		.val(conf.value)
		// widget is not append yet, so we have to propagate 'ready' event manually
		.on('ready', function () { $this.trigger('ready'); })
		// define the expected .data('value') method
		.data('value', function() {return $widget.val();});
		if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
		if(conf.required) {
            $widget.attr('aria-required', 'true');
            $widget.addClass('required');
        }
		return $widget.trigger('ready');
	},
	'integer': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.inputmask('integer',	{
			allowMinus: true
		});
	},
	'float': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.inputmask('decimal',	{
			radixPoint:	qinoa.conf.QN_NUMERIC_DECIMAL_POINT,
			digits:		qinoa.conf.QN_NUMERIC_DECIMAL_PRECISION,
			autoGroup:	false
		});
	},
	'date': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.datepicker({
			// dateFormat: 'dd/mm/yy',
			dateFormat: qinoa.conf.QN_DATE_FORMAT,
			yearRange: 'c-70:c+20',
			changeMonth: true,
			changeYear: true
		});
	},
	'datetime': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.datetimepicker({
			dateFormat: qinoa.conf.QN_DATE_FORMAT,
			timeFormat: qinoa.conf.QN_TIME_FORMAT,
			yearRange: 'c-70:c+20',
			changeMonth: true,
			changeYear: true
		});
	},
	'time': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.timepicker({timeFormat: qinoa.conf.QN_TIME_FORMAT});
	},
	'timestamp': function ($this, conf) {
//todo
	},
	'boolean': function ($this, conf) {
// todo : align checkbox left
		if(conf.mode == 'edit') {
			var $widget =	$('<input type="checkbox" value="1" />')
			.attr({id: conf.id, name: conf.name})
			.prop('checked', (parseInt(conf.value) > 0))
			.val((parseInt(conf.value) > 0)?1:0)
			.on('change', function () {
				this.value = +(this.checked);
			})
			.on('ready', function () {$this.trigger('ready');})
			.data('value', function() {return $widget.val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');
			return $widget.trigger('ready');
		}
		if(conf.mode == 'view'){
		}
	},
	'password': function ($this, conf) {
		var $widget = $('<input type="password"/>')
		.attr({id: conf.id, name: conf.name})
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		.val(conf.value)
		.on('ready', function () { $this.trigger('ready'); })
		.data('value', function() {return $widget.val();});
		if(conf.readonly) $widget.attr("disabled","disabled").addClass('ui-state-disabled');
		if(conf.required) $widget.addClass('required');
		return $widget.trigger('ready');
	},
	'short_text': function ($this, conf) {
		var $widget = $('<textarea/>')
		.attr({id: conf.id, name: conf.name})
		// set layout and use jquery-UI css
		.addClass('ui-widget')
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		.html(conf.value)
		// widget is not appended yet, so we have to propagate 'ready' event manually
		.on('ready', function () { $this.trigger('ready'); })
		.data('value', function() {return $widget.val();});
		if(conf.readonly) $widget.attr("disabled","disabled").addClass('ui-state-disabled');
		if(conf.required) $widget.addClass('required');
		return $widget.trigger('ready');
	},
	'text': function ($this, conf) {
		var $widget = $('<textarea/>')
		.hide()
		.attr({id: conf.id, name: conf.name})
//		.uniqueId()
		.html(conf.value)
		.on('ready', function () { $this.trigger('ready'); })
		.data('value', function() {return $widget.val();} );

		var $richtext =
		$('<div/>')
		.html(conf.value)
		.richtext({
			toolbar: [
				['Maximize'],['Source'],['Undo','Redo'],['Cut','Copy','Paste'],['Bold','Italic','Underline','Strike','-','Subscript','Superscript', '-', 'RemoveFormat'],
				'/',
				['TextColor'], ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote'],['Anchor','Link','Image','Table']
			]
		})
		.on('change', function () {
			$widget.val($richtext.richtext('value')).trigger('change');
		})
		.appendTo($this);

		if(conf.required) $widget.addClass('required');
		return $widget.trigger('ready');
	}
};

$.fn.qFormWidget = function(conf){

	var default_conf = {
		mode:		'edit',
		name:		'',
		value:		'',
		type:		'string',
		format:		'',
		align:		'left',
		readonly:	false,
		required:	false
	};

	return this.each(function() {
		return (function ($this, conf) {
			try {
				if(typeof qinoa.FormWidgets[conf.type] == 'undefined') throw Error('Error raised in qinoa-ui.qFormWidget : unknown type '+conf.type);
				var $widget = qinoa.FormWidgets[conf.type]($this, conf);
				return $this.data('widget', $widget.appendTo($this));
			}
			catch(e) {
				qinoa.console.log(conf.type+' '+conf.name+e.message);
			}
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery, qinoa);



/**
	Additional widgets definitions
*/
(function($, qinoa){
	qinoa.FormWidgets.many2one = function ($this, conf) {
		var default_conf = {
			view: 'list.default'
		};
		return (function ($this, conf) {
			var $widget =	$('<input type="hidden"/>')
			.attr({id: conf.id, name: conf.name})
			.val(conf.value)
			.data('value', function() {return $widget.val();});

			// obtain the fields from the specified view
			$.when(qinoa.get_fields(conf.class_name, conf.view))
			.done(function (result) {
				conf.fields = Object.keys(result);
				// create the UI

				$('<div/>')
				.css({'position': 'relative'})
				.addClass('ui-widget')
					.append(
						$('<input/>')
						.addClass('choice_input')
						.css({'box-sizing': 'border-box', 'width': 'calc(100% - 5em)', 'text-align': conf.align})
						.autocomplete({
							minLength: 4,
							delay: 500,
							source: function(request, response){
								// we limit the number of results to 25
								$.when(qinoa.search(conf.class_name, [[[conf.fields[0], 'ilike', '%' + request.term + '%']]], conf.fields[0], 'asc', 0, 25, conf.lang))
								.done(function (ids) {
									var list = [];
									if(Object.keys(ids).length > 0) {
										$.when(qinoa.read(conf.class_name, ids, conf.fields[0], conf.lang))
										.done(function (data) {
											// note: we use 'ids' order since 'data' might have been reordered by the browser
											$.each(ids, function(i, id) { list.push({label: data[id][conf.fields[0]], value: id }); });
											response(list);
										})
										.fail(function (code) { qinoa.console.log('Error raised by qinoa.read('+conf.class_name+') in qinoa.FormWidgets.many2one: '+qinoa.error_codes[code]); });
									}
								})
								.fail(function (code) { qinoa.console.log('Error raised by qinoa.search('+conf.class_name+','+conf.fields[0]+') in qinoa.FormWidgets.many2one: '+qinoa.error_codes[code]); });
							},
							select: function(event, ui) {
								// we intercept the selection in the autocomplete list in order to display the label
								// and store the id in a hidden input
								$widget.val(ui.item.value).trigger('change');
								// trigger a refresh of the displayed label and display first field in the meanwhile
								$(event.target).val(ui.item.label).trigger('feed');
								return false;
							}
						})
						.on('feed', function() {
							// render the content of the widget
							var m2o = $widget.val();
							if(m2o <= 0) $this.trigger('ready');
							else {
								// request values of the fields involved in the specified view for the selected foreign object
								$.when(qinoa.read(conf.class_name, m2o, conf.fields, conf.lang))
								.done(function (data) {
									var value = '';
									$.each(conf.fields, function (i, field) {
										if(value.length) value += ', ';
										value += data[m2o][field];
									});
									// display the resulting label
									$('.choice_input', $this).val(value).blur();
									$this.trigger('ready');
								});
							}
						})
						// initial feed
						.trigger('feed')
					)
					.append(
						// note : remember that by default buttons act like 'submit' (hence the 'type' attribute)
						$('<button type="button"/>').button({icons:{primary:'ui-icon-pencil'}, text: false})
						.attr('title', 'edit')
						.css({'position': 'absolute', 'right': '2.5em', 'top': '0', 'margin': '0', 'height': '100%'})
						.on('click', function() {
							// if an  item is selected, open an edition window
							var object_id = $widget.val();
							// var view = (conf.view != undefined)?conf.view:'form.default';
							// default view is probabily intended for grid view
							var view = 'form.default';
							var $form = $('<form/>')
							.qForm($.extend(true, conf, {
								view: view,
								object_id: object_id,
								predefined: {
									class_name: conf.class_name,
									ids: object_id,
									lang: conf.lang
								}
							}))
							.on('ready', function() {
								$dia = qinoa.dialog({
									content:	$form,
									title:		'Object edition: '+conf.class_name+' ('+object_id+') - '+view,
									buttons:	[]
								})
								.on('formclose', function(event, action) {
									if(typeof action != 'undefined' && action != 'cancel') {
										$('.choice_input', $this).trigger('feed');
									}
									$dia.trigger('dialogclose');
								});
							});
						})
					)
					.append(
						$('<button type="button"/>').button({icons:{primary:'ui-icon-search'}, text: false})
						.attr('title', 'search')
						.css({'position': 'absolute', 'right': '0', 'top': '0', 'margin': '0', 'height': '100%'})
						.on('click', function() {
	// todo : use the current content as a mask for searching among the item
							// create a domain with ilike operator
							// and display a selection list
							// copy the current config and set list for unique selection
							var grid_conf = $.extend(true, {'multiple': false}, conf);
							var $grid =
							$('<div/>')
							.qSearchGrid(grid_conf)
							.on('ready', function() {
								qinoa.dialog({
									content:	$grid,
									title:			'Choose item',
									buttons:	[{
										text: "Ok",
										click: function() {
											$.each($grid.data('conf').selection, function(id, state){
												$widget.val(id);
												$this.trigger('change');
												$('.choice_input', $this).trigger('feed');
											});
											$( this ).trigger('dialogclose');
										}
									}]
								});
							});
						})
					)
				.appendTo($this);
			});

			return $widget;
		})($this, $.extend(true, default_conf, conf));
	};


	qinoa.FormWidgets.one2many = function ($this, conf) {
		var $widget =	$('<input type="hidden"/>')
		.attr({id: conf.id, name: conf.name})
		.val(conf.value)
		.data('value', function() {return $widget.val();} );

		(function($this, conf) {
			$.when(qinoa.get_schema(conf.parent_class))
			.done(function (schema) {
				var domain = [[ [schema[conf.name]['foreign_field'], '=', conf.parent_id] ]];
				if(conf.domain != undefined) domain = merge_domains(domain, conf.domain);
				var grid_conf = $.extend(true, conf, {
					domain: 	domain,
					buttons:	{
						edit:	{
									text: 'edit',
									icon: 'ui-icon-pencil',
								},
						del:	{
									text: 'delete relation',
									icon: 'ui-icon-minus',
								},
						add:	{
									text: 'add relation',
									icon: 'ui-icon-plus',
								}
					},
					actions:	{
						add:	function($grid, conf) {
							var grid_conf = {class_name: conf.class_name, view: conf.views.add, lang: conf.lang};
//							if(conf.domain != undefined) grid_conf.domain = eval(conf.domain);
							// display only items not already present in relation
							grid_conf.domain = [[ [schema[conf.name]['foreign_field'], '<>', conf.parent_id] ]];
							var $sub_grid =
							$('<div/>')
							.qSearchGrid(grid_conf)
							.on('ready', function() {
								qinoa.dialog({
									content:	$sub_grid,
									title:		'Add relation',
									buttons:	[{
										text: "Ok",
										click: function() {
											$.each($sub_grid.data('conf').selection, function(id, state){
												conf.more = add_value(conf.more, id);
												conf.less = remove_value(conf.less, id);
											});
                                            // force grid to refresh its content
                                            $grid.trigger('reload');                                            
                                            // update the value of the widget
                                            $grid.trigger('change');                                            
											$( this ).trigger('dialogclose');
										}
									}]
								});
							});
					},
					del:	function($grid, conf) {
							var ids = Object.keys(conf.selection);
							$.when(qinoa.confirm({
									message:	'<p><b>'+ ids.length +' item(s) selected.</b></p>'+
												'Do you confirm deletion for selected relations(s) ?',
									title:		'Deletion'
							}))
							.done(function() {
								$.each(conf.selection, function(id, state){
									conf.less = add_value(conf.less, id);
									conf.more = remove_value(conf.more, id);
								});
								// force grid to refresh its content
								$grid.trigger('reload');
								// update the value of the widget
								$grid.trigger('change');
							});
					}
					}
				});
				var $grid =
				$('<div/>')
				.qGrid(grid_conf)
				.on('ready', function() {
					$this.trigger('ready');
				})
				.on('change', function () {
					var value = $grid.data('conf').more.toString();
					$.each($grid.data('conf').less, function() {
						if(value.length > 0) value += ',';
						value += '-'+conf.less[i];
					});
					$widget.val(value);
					$this.trigger('change');
				})
				.appendTo($this);
			});
		})($this, conf);
		return $widget;
	};

	qinoa.FormWidgets.many2many = function ($this, conf) {
		var $widget =	$('<input type="hidden"/>')
		.attr({id: conf.id, name: conf.name})
		.val(conf.value)
		.on('change', function () { $this.trigger('change'); })
		.data('value', function() {return $widget.val();});

		(function($this, conf) {
			$.when(qinoa.get_schema(conf.parent_class))
			.done(function (schema) {
				var domain = [[ [schema[conf.name]['foreign_field'], 'contains', conf.parent_id] ]];
				if(conf.domain != undefined) domain = merge_domains(domain, conf.domain);
				var grid_conf = $.extend(true, conf, {
					domain: 	domain,
					buttons:	{
						edit:	{
									text: 'edit',
									icon: 'ui-icon-pencil',
								},
						del:	{
									text: 'delete relation',
									icon: 'ui-icon-minus',
								},
						add:	{
									text: 'add relation',
									icon: 'ui-icon-plus',
								}
					},
					actions:	{
						add:	function($grid, conf) {
							var grid_conf = {class_name: conf.class_name, view: conf.views.add, lang: conf.lang};
//							if(conf.domain != undefined) grid_conf.domain = eval(conf.domain);
							// display only items not already present in relation
							// doesn't work this wat
							// grid_conf.domain = [[ [schema[conf.name]['foreign_field'], 'not in', [conf.parent_id]] ]];
							// we could manully prevent dislay of the objecs already in the relation
							// grid_conf.less =
							var $sub_grid =
							$('<div/>')
							.qSearchGrid(grid_conf)
							.on('ready', function() {
								qinoa.dialog({
									content:	$sub_grid,
									title:		'Add relation',
									buttons:	[{
										text: "Ok",
										click: function() {
											$.each($sub_grid.data('conf').selection, function(id, state){
												conf.more = add_value(conf.more, id);
												conf.less = remove_value(conf.less, id);
											});
                                            // force grid to refresh its content
                                            $grid.trigger('reload');                                            
                                            // update the value of the widget
                                            $grid.trigger('change');
											$( this ).trigger('dialogclose');
										}
									}]
								});
							});
                        },
                        del:	function($grid, conf) {
                            var ids = Object.keys(conf.selection);
                            $.when(qinoa.confirm({
                                    message:	'<p><b>'+ ids.length +' item(s) selected.</b></p>'+
                                                'Do you confirm deletion for selected relations(s) ?',
                                    title:		'Deletion'
                            }))
                            .done(function() {
                                $.each(conf.selection, function(id, state){
                                    conf.less = add_value(conf.less, id);
                                    conf.more = remove_value(conf.more, id);
                                });
                                // force grid to refresh its content
                                $grid.trigger('reload');
                                // update the value of the widget
                                $grid.trigger('change');
                            });
                        }
					}
				});
				var $grid =
				$('<div/>')
				.qGrid(grid_conf)
				.on('ready', function() {
					$this.trigger('ready');
				})
				.on('change', function () {
					var value = $grid.data('conf').more.toString();
					$.each($grid.data('conf').less, function() {
						if(value.length > 0) value += ',';
						value += '-'+conf.less[i];
					});
					$widget.val(value).trigger('change');
				})
				.appendTo($this);
			});
		})($this, conf);
		return $widget;
	};

	qinoa.FormWidgets.binary = function ($this, conf) {
		var $widget = $('<input type="file" />')
		.attr({id: conf.name, name: conf.name})
		.addClass('ui-widget')
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		// widget is not appended yet, so we have to propagate 'ready' event manually
		.on('ready', function () { $this.trigger('ready'); })
		// define the expected .data('value') method
		.data('value', function() {return $widget.val();});
		return $widget.trigger('ready');
	};

	qinoa.FormWidgets.image = function ($this, conf) {
		var $widget =	$('<input type="hidden"/>')
						.attr({id: conf.id, name: conf.name})
						.val(conf.value)
						.on('change ready', function (event) { $this.trigger(event.type);})
						.data('value', function() {return $widget.val();});
		return $widget.trigger('ready');
	};

})(jQuery, qinoa);
/*! 
* qinoa-ui.qForm - v1.0.0
* https://github.com/cedricfrancoys/qinoa
* Copyright (c) 2015 Cedric Francoys; Licensed GPLv3 */

/**
 * qinoa-ui.qForm : A plugin generating Form view controls
 *
 * Author	: Cedric Francoys
 * Launch	: March 2015
 * Version	: 1.0
 *
 * Licensed under GPL Version 3 license
 * http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
(function($, qinoa){
"use strict";
	$.fn.qForm = function(conf){

		var default_conf = {
		// mandatory params
			class_name: '',						// class of the object to edit
			object_id: 0,						// id of the object to edit
		// optional params
			view: 'form.default',				// view to use for object edition
			lang: qinoa.conf.content_lang,	    // language in which request the content to server
			ui: qinoa.conf.user_lang,			// language in which display UI texts
			predefined: {},						// assign predefined values to some fields or insert hidden controls when those fields are not present in selected view
			autosave: true,						// autosaving drafts of the object being edited
			success_handler: null,				// bypass the standard action listener and execute some function in case of success
		// internal params
			modified: false						// status of the object being edited
		};

		var methods = {

			/**
			* Retrieves the html source of the requested view
			*
			*/
			load_view : function($form, conf) {
				var deferred = $.Deferred();
				$.when(qinoa.get_view(conf.class_name, conf.view))
				.done(function (view_html) {
					var $view = $(view_html);
					// extend the configuration object with 'view' tag attributes, if any
					// note: attributes we should expect are: 'action', 'domain', 'orientation'
					$.each($view[0].attributes, function(i, attr) { conf[attr.name] = attr.value; });
					// we'll need the form id in the 'adapt_view' method
					$view.attr('id', $form.attr('id'));
					var $result = methods.adapt_view($view);
					// if an 'action' is defined
					if(conf.action !== undefined) {
						// append buttons to the form
						$result
						.append($('<div/>').attr('align', 'right').attr('width', '100%')
							.append($('<button type="button" />').attr('name', 'save').attr('action', conf.action).attr('default', 'true'))
							.append($('<button type="button" />').attr('name', 'cancel').attr('action', 'cancel'))
						);
	/*
						if(conf.autosave)
							$form.append($('<button type="button" />').css('display', 'none').attr('name', 'autosave').attr('action', 'core_draft_save'));
	*/
					}
					// append the view to the form but don't show it yet
					$form.addClass('ui-form ui-front').append($result.addClass('qView').hide());
					deferred.resolve();
				});
				return deferred.promise();
			},



			/**
			* Transforms the html source if necessary (convert DIVs and SPANs to tables)
			* Returns a jQuery object
			*/
			adapt_view : function($view) {
				//we use tables for easier rendering
				var convert_to_table = function($item) {
					var html = $item.html();
					// labels
					html = html.replace(/(<label[^>]*>[^<]*<\/label>)/gi, '<td class="label">$1</td>');
					// vars
					html = html.replace(/(<var[^>]*>[^<]*<\/var>)/gi, '<td class="field">$1</td>');
					// table wrap + newlines
					html = '<table><tr>' + html.replace(/<br[^\/>]*>/gi, '</tr><tr>') + '</tr></table>';
					$item.html(html);
					// colspan check
					$item.find('label,var').each(function() {
						var colspan = $item.attr('colspan');
						if(typeof(colspan) != 'undefined') $item.parent().attr('colspan', colspan);
					});
					return $item.html();
				};

				// we use a recusive function to enrich html templates
				var transform_html = function($elem) {
					var $result = $('<div/>');
					// 1) process the sections of the current node
					var tabs_already_added = false;
					var $tabs_list = $('<ul/>');
					var $tabs_pane = $('<div/>').attr('id', $view.attr('id')+'_tabs').addClass('qtab');

					var $sections = $elem.children('section').each(function() {
						var name = $(this).attr('name');
						// we put a label inside the tab for later translation
						var $new_item = $('<li/>').append($('<a/>').attr('href','#'+name+'_tab').append($('<label/>').attr('name', name)));
						var $inner_item = transform_html($(this));
						var $new_tab = $('<div/>').attr('id', name+'_tab');
						$new_tab.append($('<table/>').append($('<tr/>').append($('<td/>').addClass('field').append($inner_item))));
						$tabs_list.append($new_item);
						$tabs_pane.append($new_tab);
					});
					$tabs_pane.prepend($tabs_list);

					// 2) process other elements
					$elem.children().each(function () {
						switch($(this).prop('nodeName').toLowerCase()) {
							case 'fieldset':
								var $new_fieldset = $('<fieldset/>');
								var title = $(this).attr('title');
								if(title !== undefined) $new_fieldset.append($('<legend/>').attr('name', title));
								$result.append($new_fieldset.append(transform_html($(this))));
								break;
							case 'span':
								var $new_div = $('<div/>');
								var width = $(this).attr('width');
								var align = $(this).attr('align');
								width = (width === undefined)?100:parseInt(width);
								align = (align === undefined)?'left':align;
								$new_div.css('float', 'left').css('text-align', align).css('width', (width-2) + '%').css('padding-left', '1%').css('padding-right', '1%');
								$result.append($new_div.append(convert_to_table($(this))));
								break;
							case 'div':
								var $new_div = $('<div/>');
								var width = $(this).attr('width');
								var align = $(this).attr('align');
								var id = $(this).attr('id');
								width = (width === undefined)?100:parseInt(width);
								align = (align === undefined)?'left':align;
								if(id !== undefined) $new_div.attr('id', id);
								$new_div.css('float', 'left').css('text-align', align).css('width', width + '%');
								$result.append($new_div.append(transform_html($(this))));
								break;
							case 'section':
								if(tabs_already_added) break;
								$result.append($tabs_pane);
								tabs_already_added = true;
								break;
							default:
								$result.append($(this));
								break;
						}
					});
					return $result.children();
				};
				return transform_html($view);
			},



			/**
			* Loads and inserts object values into the form
			*
			*/
			feed : function($form, conf) {
				var deferred = $.Deferred();
				var schema, fields;
				$({})
				// get the object schema
				.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}); })
				// get the list of the fields present in the specified view
				.queue(	function (next) { $.when(qinoa.get_fields(conf.class_name, conf.view)).done(function (result) {fields = result; next(); }); })
				.queue( function (next) {
					var requested_fields = [];
					// we request only simple fields : complex fields loading is handled by their related widget
					$.each(fields, function (field, attributes) {
						if($.inArray(schema[field].type, qinoa.simple_types) >= 0) requested_fields.push(field);
					});
					$.when(qinoa.read(conf.class_name, conf.object_id, requested_fields, conf.lang))
					.done(function (result) {
						if(typeof result[conf.object_id] != 'undefined') {
							$.each(result[conf.object_id], function (field, value) {
								// store temporarily value as a data property (will be fetched by 'render' method)
								$('#'+field, $form).data('value', value);
							});
						}
						deferred.resolve();
						next();
					});
				});
				return deferred.promise();
			},



			/**
			* Generates form widgets
			* Convert VARs tag into widgets, add buttons and hide invisible items
			*
			*/
			render: function($form, conf) {
				var deferred = $.Deferred();


				// handle visibilty attribute
				$('var,label,button,section,div,span', $form).each(function() {
					var attr_visible = $(this).attr('visible');
					// hide non-visible items
					if(attr_visible !== undefined && !eval(attr_visible)) $(this).hide();

				});


				// enable jQuery UI widgets

				// enable tabs
				$('.qtab', $form).tabs();
				// enable buttons
				$('button[action],button[show]', $form).each(function() {
					var $this = $(this).button();
					if($this.attr('action') !== undefined) {
						$this.on('click', function () { $form.trigger('submit', $this.attr('action')); });
					}
					if($this.attr('show') !== undefined) {
						var view_attr	= ($this.attr('view') === undefined)?'form.default':$this.attr('view');
						var output_attr	= ($this.attr('output') === undefined)?'html':$this.attr('output');
						$this.on('click', function () {
							// open new window and transmit the current context
							window.open('index.php?show='+$this.attr('show')+'&'+$.param({
									view: view_attr,
									id: conf.object_id,
									object_class: conf.class_name,
									output: output_attr
								})
							);
						});
					}
					if($this.attr('default') == 'true') {
						$this.focus();
					}

/*
					// if we need to auto-save drafts, set the timeout handle
					if($(this).attr('name') == 'autosave') {
						var autosaving = function(){
							if(conf.modified) {
								// we simulate a click on the button
								$(this).trigger('click');
								// and reset the modification flag
								conf.modified = false;
							}
							conf.timer_id = setTimeout(autosaving, easyObject.conf.auto_save_delay * 60000);
						}
						// init timer
						conf.timer_id = setTimeout(autosaving, easyObject.conf.auto_save_delay * 60000);
					}
*/
				});


				// generate widgets
				var schema, fields;
				$({})
				// load schema
				.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}); })
				// load fields and their attributes
				.queue(	function (next) { $.when(qinoa.get_fields(conf.class_name, conf.view)).done(function (result) {fields = result; next(); }); })
				// instanciate the widgets
				.queue( function (next) {

					// initialize form callbacks handler
					conf.onSubmitCallbacks = $.Callbacks();
					conf.onSubmitResult = true;

					// insert some hidden controls (predefined fields not present in the specified view)
					// and set pedefined values if any
					if(typeof conf.predefined == 'object') {
						$.each(conf.predefined, function(field, value){
							if($.inArray(field, Object.keys(fields)) < 0) {
								if(typeof value == 'object') field += '[]';
								$form.append($('<input type="hidden"/>').attr({id: field+(new Date()).getTime(), name: field, value: value}));
							}
						});
					}
					// define an array to keep track of the widgets ready to be displayed
					conf.ready = {};
					// generate widgets
					$.each(fields, function (field, attributes){
						// copy var attributes to a configuration object
						// note: possible attributes are : 'readonly', 'required', 'onchange', 'onsubmit', 'view', 'domain', 'widget'
						var config = $.extend({}, attributes);
						var $item = $('#'+field, $form)
						// listen to ready event
						// note : we have to listen before calling qFormWidget since some widgets can be immediately ready
						.on('ready', function(event) {
							// don't trigger the ready event twice for the same widget
							if(typeof conf.ready[field] != 'undefined') return false;
							conf.ready[field] = true;
							// prevent propagation while some widget are still missing
							if(Object.keys(conf.ready).length < Object.keys(fields).length) return false;
						});
						// extend config with class name for complex types
						if(typeof schema[field].foreign_object != 'undefined') config.class_name = schema[field].foreign_object;
						// create the widget (extend/erase some properties)
						$item.qFormWidget($.extend(config, {
								id:				field+(new Date()).getTime(),
								name:			field,
								type: 			(typeof attributes.widget != 'undefined')?attributes.widget:schema[field].type,
								value:			$item.data('value'),
								parent_class:	conf.class_name,
								parent_id:		conf.object_id,
								lang: 			conf.lang,
								ui:				conf.ui
							})
						)
						.one('change', function() {
							conf.modified = true;
							qinoa.console.log('Change made to edited object ('+conf.class_name+', '+conf.object_id+') on field '+field);
							if(typeof attributes.onchange != 'undefined') {
								// we don't use $.globalEval because we need access to the current context
								eval(attributes.onchange);
							}
						});

						// re-submission of empty binary fields would result in erasing existing data!
						if(schema[field].type == 'binary') {
							conf.onSubmitCallbacks.add(function() {
								if($item.data('widget').val().length === 0) $item.data('widget').attr({id: '', name: ''});
							});
						}

						// add onSubmit callback to the form, if any
						if(attributes.onsubmit) {
							conf.onSubmitCallbacks.add(function() {
								// we don't use $.globalEval because we need access to the current context
								eval(attributes.onsubmit);
							});
						}
						if(attributes.required) {
// note : marking a binary field as required might be a problem when re-editing
							// add a callback to ensure field is not empty when submitting
							conf.onSubmitCallbacks.add(function() {
//								if($.proxy($item.data('value'), $item)().length <= 0) {
								if($item.data('widget').data('value')().length <= 0) {
									// if a required field is empty at submission, mark it as invalid
									$item.data('widget').addClass('invalid');
									conf.onSubmitResult = false;
								}
								else $item.data('widget').removeClass('invalid');
							});
						}


						// remove attributes that might cause undesired effects
						$item.removeAttr('onsubmit');
						$item.removeAttr('onchange');
						$item.removeAttr('id');
					});
					deferred.resolve();
					next();
				});
				return deferred.promise();
			},


			/**
			* Translates displayed labels, legends and buttons into user's lang (if defined in i18n folder)
			* and adds help tips in selected lanuage
			*/
			translate: function($form, conf){
				var deferred = $.Deferred();
				$.when(qinoa.get_lang(conf.class_name, conf.ui))
				.done(function (lang) {
					if(typeof lang != 'object' || $.isEmptyObject(lang)) {
						// 1) stand-alone labels, legends, buttons (refering to the current view)
						$('label[name],legend[name],button[name]', $form).each(function() {
							$(this).text(ucfirst($(this).attr('name')));
						});
						// 2) field labels
						$('label[for]', $form).each(function() {
							$(this).text(ucfirst($(this).attr('for')));
						});
					}
					else {
						// 1) stand-alone labels, legends, buttons (refering to the current view)
						$('label[name],legend[name],button[name]', $form).each(function() {
							var name = $(this).attr('name');
							if(typeof name != 'undefined') {
								if(typeof lang.view[name] != 'undefined') {
									$(this).text(lang.view[name].label);
								}
								else $(this).text(ucfirst(name));

							}
						});
						// 2) field labels
// todo : not necesarily related to the object being edited : may also be of a subitem (what if parent and child have a field of the same name ?)
						$('label[for]', $form).each(function() {
							var value;
							var field = $(this).attr('for');
							if(field !== undefined) {
								if(typeof lang.model[field] != 'undefined' && typeof lang.model[field].label != 'undefined') {
									$(this).text(lang.model[field].label);
									if(typeof lang.model[field].help != 'undefined') {
										$(this).append($('<sup/>').attr('title', lang.model[field].help.replace(/\n/g,'<br />')).addClass('help').text('?').tooltip());
									}
								}
								else $(this).text(ucfirst(field));
							}
						});
					}
					deferred.resolve();
				});
				return deferred.promise();
			},


			/**
			* Starts listener for the submit event and handle form actions
			*
			*/
			listen: function($form, conf) {
				var deferred = $.Deferred();
				// we hijack the default submit event to handle actions and to be able to submit files ('binary' type) by posting multipart/form-data content
				$form.on('submit', function(event, action){
					// flag telling if we have to execute action silently
					var silent = false;
					var close = function(action, msg) {
						if($form.parent().parent().hasClass('ui-dialog')) { // form is inside a dialog
							$form.parent().trigger('formclose', action);
							$form.remove();
							// go to top of page
							$('html, body').animate({ scrollTop: 0 }, 0);
						}
						else if(typeof msg != 'undefined') qinoa.console.log(msg);
						return false;
					};

					// check requested action
					switch(action) {
						case 'apply':
						case 'core_draft_save':
										silent = true;
										break;
						case 'core_objects_write':
						case 'core_objects_update':
										silent = false;
										break;
						case 'cancel':
										return close('cancel');
						default:
										alert('No action is attached to this button.');
										return false;
					}

					// 1) check submission callbacks (tasks that must be processed before the form submission)
					// onSubmit callbacks are used to :
					// - check fields validity
					// - execute user defined functions (set in views, using 'onSubmit' attribute) that could modify some data
					conf.onSubmitResult = true;
					conf.onSubmitCallbacks.fire();
					if(!conf.onSubmitResult) {
						// something went wrong : stop the form submission
						alert('A mandatory field is left blank.');
						qinoa.console.log('One of the submission callbacks failed');
						return false;
					}


					// 2) POST the form data
					// force vars to synchronize with their widget if necessary (mandatory for textarea)
					$form.serialize();
					$.ajax({
						url: 'index.php?do='+action,
						type: 'POST',
						async: true,
                        dataType: 'json',
						data: new FormData($form[0]),
						cache: false,
						contentType: false,
						processData: false
					})
					.done(function(data) {
						// convert returned string to js object
						if(typeof data.result == 'number') {
							qinoa.console.log('Error raised in qinoa-ui.qForm by action ('+action+'): '+qinoa.error_codes[data.result]);
							if(data.result == qinoa.conf.INVALID_PARAM) {
								$.when(qinoa.get_lang(conf.class_name, conf.ui))
								.done(function (lang) {
									// get an array of messages for the current language
									var message = qinoa.error_codes[data.result];
									$.each(data.error_message_ids, function (index, item) {
										if(typeof lang == 'object' && typeof lang.view[item] != 'undefined') message += lang.view[item].label + "\n";
										else message += item + "\n";
									});
// todo : translation
									qinoa.alert(message, 'Validation error');
								});
							}
						}
						else {
							if(typeof conf.success_handler == 'function') {
								conf.success_handler(data);
							}
							else {
								if(silent){
									qinoa.console.log('Action ' + action + ' successfuly executed');
								}
								else {
									// if action indicates a redirection, go to the new location
									if(typeof data.url != 'undefined' && data.url.length > 0) window.location.href = data.url;
									// otherwise request parent dialog to close, if any
									else close(action, 'Action '+ action +' successfuly executed');
								}
							}
						}

					})
					.fail(function(data){
						qinoa.console.log('Error raised by action ('+action+'): '+qinoa.error_codes[data.result]);
					});
					// prevent original event handler
					return false;
				});
				deferred.resolve();
				return deferred.promise();
			}
		};


		return this.each(function() {
			return (function ($this, conf) {

				$.when(methods.load_view($this, conf))
				.done(function () { return methods.translate($this, conf); })
				.then(function () { return methods.feed($this, conf); })
				.then(function () { return methods.render($this, conf); })
				.then(function () { return methods.listen($this, conf); });

				return $this.on('ready', function() {
					$('.qView', $this).show();
				});
			})($(this), $.extend(true, default_conf, conf));
		});
	};

	/**
	* Extend qinoa with a .form method
	* Calling this method will display a loading indicator while building the form.
	*/
	$.extend(true, qinoa, {
		form: function(conf) {
			qinoa.loader.show($('body'));
			var $form = $('<form/>')
			.qForm(conf)
			.on('ready', function() {
				qinoa.loader.hide();
			});
			return $form;
		}
	});

})(jQuery, qinoa);

/*! 
* rangy-core.js - v1.3.0-beta.2
* https://github.com/timdown/rangy
* Copyright (c) 2015 - Tim Down; Licensed MIT licence */

/**
 * Rangy, a cross-browser JavaScript range and selection library
 * https://github.com/timdown/rangy
 *
 * Copyright 2015, Tim Down
 * Licensed under the MIT license.
 * Version: 1.3.0-beta.2
 * Build date: 22 March 2015
 */

(function(factory, root) {
    if (typeof define == "function" && define.amd) {
        // AMD. Register as an anonymous module.
        define(factory);
    } else if (typeof module != "undefined" && typeof exports == "object") {
        // Node/CommonJS style
        module.exports = factory();
    } else {
        // No AMD or CommonJS support so we place Rangy in (probably) the global variable
        root.rangy = factory();
    }
})(function() {

    var OBJECT = "object", FUNCTION = "function", UNDEFINED = "undefined";

    // Minimal set of properties required for DOM Level 2 Range compliance. Comparison constants such as START_TO_START
    // are omitted because ranges in KHTML do not have them but otherwise work perfectly well. See issue 113.
    var domRangeProperties = ["startContainer", "startOffset", "endContainer", "endOffset", "collapsed",
        "commonAncestorContainer"];

    // Minimal set of methods required for DOM Level 2 Range compliance
    var domRangeMethods = ["setStart", "setStartBefore", "setStartAfter", "setEnd", "setEndBefore",
        "setEndAfter", "collapse", "selectNode", "selectNodeContents", "compareBoundaryPoints", "deleteContents",
        "extractContents", "cloneContents", "insertNode", "surroundContents", "cloneRange", "toString", "detach"];

    var textRangeProperties = ["boundingHeight", "boundingLeft", "boundingTop", "boundingWidth", "htmlText", "text"];

    // Subset of TextRange's full set of methods that we're interested in
    var textRangeMethods = ["collapse", "compareEndPoints", "duplicate", "moveToElementText", "parentElement", "select",
        "setEndPoint", "getBoundingClientRect"];

    /*----------------------------------------------------------------------------------------------------------------*/

    // Trio of functions taken from Peter Michaux's article:
    // http://peter.michaux.ca/articles/feature-detection-state-of-the-art-browser-scripting
    function isHostMethod(o, p) {
        var t = typeof o[p];
        return t == FUNCTION || (!!(t == OBJECT && o[p])) || t == "unknown";
    }

    function isHostObject(o, p) {
        return !!(typeof o[p] == OBJECT && o[p]);
    }

    function isHostProperty(o, p) {
        return typeof o[p] != UNDEFINED;
    }

    // Creates a convenience function to save verbose repeated calls to tests functions
    function createMultiplePropertyTest(testFunc) {
        return function(o, props) {
            var i = props.length;
            while (i--) {
                if (!testFunc(o, props[i])) {
                    return false;
                }
            }
            return true;
        };
    }

    // Next trio of functions are a convenience to save verbose repeated calls to previous two functions
    var areHostMethods = createMultiplePropertyTest(isHostMethod);
    var areHostObjects = createMultiplePropertyTest(isHostObject);
    var areHostProperties = createMultiplePropertyTest(isHostProperty);

    function isTextRange(range) {
        return range && areHostMethods(range, textRangeMethods) && areHostProperties(range, textRangeProperties);
    }

    function getBody(doc) {
        return isHostObject(doc, "body") ? doc.body : doc.getElementsByTagName("body")[0];
    }

    var forEach = [].forEach ?
        function(arr, func) {
            arr.forEach(func);
        } :
        function(arr, func) {
            for (var i = 0, len = arr.length; i < len; ++i) {
                func(arr[i], i);
            }
        };

    var modules = {};

    var isBrowser = (typeof window != UNDEFINED && typeof document != UNDEFINED);

    var util = {
        isHostMethod: isHostMethod,
        isHostObject: isHostObject,
        isHostProperty: isHostProperty,
        areHostMethods: areHostMethods,
        areHostObjects: areHostObjects,
        areHostProperties: areHostProperties,
        isTextRange: isTextRange,
        getBody: getBody,
        forEach: forEach
    };

    var api = {
        version: "1.3.0-beta.2",
        initialized: false,
        isBrowser: isBrowser,
        supported: true,
        util: util,
        features: {},
        modules: modules,
        config: {
            alertOnFail: false,
            alertOnWarn: false,
            preferTextRange: false,
            autoInitialize: (typeof rangyAutoInitialize == UNDEFINED) ? true : rangyAutoInitialize
        }
    };

    function consoleLog(msg) {
        if (typeof console != UNDEFINED && isHostMethod(console, "log")) {
            console.log(msg);
        }
    }

    function alertOrLog(msg, shouldAlert) {
        if (isBrowser && shouldAlert) {
            alert(msg);
        } else  {
            consoleLog(msg);
        }
    }

    function fail(reason) {
        api.initialized = true;
        api.supported = false;
        alertOrLog("Rangy is not supported in this environment. Reason: " + reason, api.config.alertOnFail);
    }

    api.fail = fail;

    function warn(msg) {
        alertOrLog("Rangy warning: " + msg, api.config.alertOnWarn);
    }

    api.warn = warn;

    // Add utility extend() method
    var extend;
    if ({}.hasOwnProperty) {
        util.extend = extend = function(obj, props, deep) {
            var o, p;
            for (var i in props) {
                if (props.hasOwnProperty(i)) {
                    o = obj[i];
                    p = props[i];
                    if (deep && o !== null && typeof o == "object" && p !== null && typeof p == "object") {
                        extend(o, p, true);
                    }
                    obj[i] = p;
                }
            }
            // Special case for toString, which does not show up in for...in loops in IE <= 8
            if (props.hasOwnProperty("toString")) {
                obj.toString = props.toString;
            }
            return obj;
        };

        util.createOptions = function(optionsParam, defaults) {
            var options = {};
            extend(options, defaults);
            if (optionsParam) {
                extend(options, optionsParam);
            }
            return options;
        };
    } else {
        fail("hasOwnProperty not supported");
    }

    // Test whether we're in a browser and bail out if not
    if (!isBrowser) {
        fail("Rangy can only run in a browser");
    }

    // Test whether Array.prototype.slice can be relied on for NodeLists and use an alternative toArray() if not
    (function() {
        var toArray;

        if (isBrowser) {
            var el = document.createElement("div");
            el.appendChild(document.createElement("span"));
            var slice = [].slice;
            try {
                if (slice.call(el.childNodes, 0)[0].nodeType == 1) {
                    toArray = function(arrayLike) {
                        return slice.call(arrayLike, 0);
                    };
                }
            } catch (e) {}
        }

        if (!toArray) {
            toArray = function(arrayLike) {
                var arr = [];
                for (var i = 0, len = arrayLike.length; i < len; ++i) {
                    arr[i] = arrayLike[i];
                }
                return arr;
            };
        }

        util.toArray = toArray;
    })();

    // Very simple event handler wrapper function that doesn't attempt to solve issues such as "this" handling or
    // normalization of event properties
    var addListener;
    if (isBrowser) {
        if (isHostMethod(document, "addEventListener")) {
            addListener = function(obj, eventType, listener) {
                obj.addEventListener(eventType, listener, false);
            };
        } else if (isHostMethod(document, "attachEvent")) {
            addListener = function(obj, eventType, listener) {
                obj.attachEvent("on" + eventType, listener);
            };
        } else {
            fail("Document does not have required addEventListener or attachEvent method");
        }

        util.addListener = addListener;
    }

    var initListeners = [];

    function getErrorDesc(ex) {
        return ex.message || ex.description || String(ex);
    }

    // Initialization
    function init() {
        if (!isBrowser || api.initialized) {
            return;
        }
        var testRange;
        var implementsDomRange = false, implementsTextRange = false;

        // First, perform basic feature tests

        if (isHostMethod(document, "createRange")) {
            testRange = document.createRange();
            if (areHostMethods(testRange, domRangeMethods) && areHostProperties(testRange, domRangeProperties)) {
                implementsDomRange = true;
            }
        }

        var body = getBody(document);
        if (!body || body.nodeName.toLowerCase() != "body") {
            fail("No body element found");
            return;
        }

        if (body && isHostMethod(body, "createTextRange")) {
            testRange = body.createTextRange();
            if (isTextRange(testRange)) {
                implementsTextRange = true;
            }
        }

        if (!implementsDomRange && !implementsTextRange) {
            fail("Neither Range nor TextRange are available");
            return;
        }

        api.initialized = true;
        api.features = {
            implementsDomRange: implementsDomRange,
            implementsTextRange: implementsTextRange
        };

        // Initialize modules
        var module, errorMessage;
        for (var moduleName in modules) {
            if ( (module = modules[moduleName]) instanceof Module ) {
                module.init(module, api);
            }
        }

        // Call init listeners
        for (var i = 0, len = initListeners.length; i < len; ++i) {
            try {
                initListeners[i](api);
            } catch (ex) {
                errorMessage = "Rangy init listener threw an exception. Continuing. Detail: " + getErrorDesc(ex);
                consoleLog(errorMessage);
            }
        }
    }

    // Allow external scripts to initialize this library in case it's loaded after the document has loaded
    api.init = init;

    // Execute listener immediately if already initialized
    api.addInitListener = function(listener) {
        if (api.initialized) {
            listener(api);
        } else {
            initListeners.push(listener);
        }
    };

    var shimListeners = [];

    api.addShimListener = function(listener) {
        shimListeners.push(listener);
    };

    function shim(win) {
        win = win || window;
        init();

        // Notify listeners
        for (var i = 0, len = shimListeners.length; i < len; ++i) {
            shimListeners[i](win);
        }
    }

    if (isBrowser) {
        api.shim = api.createMissingNativeApi = shim;
    }

    function Module(name, dependencies, initializer) {
        this.name = name;
        this.dependencies = dependencies;
        this.initialized = false;
        this.supported = false;
        this.initializer = initializer;
    }

    Module.prototype = {
        init: function() {
            var requiredModuleNames = this.dependencies || [];
            for (var i = 0, len = requiredModuleNames.length, requiredModule, moduleName; i < len; ++i) {
                moduleName = requiredModuleNames[i];

                requiredModule = modules[moduleName];
                if (!requiredModule || !(requiredModule instanceof Module)) {
                    throw new Error("required module '" + moduleName + "' not found");
                }

                requiredModule.init();

                if (!requiredModule.supported) {
                    throw new Error("required module '" + moduleName + "' not supported");
                }
            }

            // Now run initializer
            this.initializer(this);
        },

        fail: function(reason) {
            this.initialized = true;
            this.supported = false;
            throw new Error(reason);
        },

        warn: function(msg) {
            api.warn("Module " + this.name + ": " + msg);
        },

        deprecationNotice: function(deprecated, replacement) {
            api.warn("DEPRECATED: " + deprecated + " in module " + this.name + "is deprecated. Please use " +
                replacement + " instead");
        },

        createError: function(msg) {
            return new Error("Error in Rangy " + this.name + " module: " + msg);
        }
    };

    function createModule(name, dependencies, initFunc) {
        var newModule = new Module(name, dependencies, function(module) {
            if (!module.initialized) {
                module.initialized = true;
                try {
                    initFunc(api, module);
                    module.supported = true;
                } catch (ex) {
                    var errorMessage = "Module '" + name + "' failed to load: " + getErrorDesc(ex);
                    consoleLog(errorMessage);
                    if (ex.stack) {
                        consoleLog(ex.stack);
                    }
                }
            }
        });
        modules[name] = newModule;
        return newModule;
    }

    api.createModule = function(name) {
        // Allow 2 or 3 arguments (second argument is an optional array of dependencies)
        var initFunc, dependencies;
        if (arguments.length == 2) {
            initFunc = arguments[1];
            dependencies = [];
        } else {
            initFunc = arguments[2];
            dependencies = arguments[1];
        }

        var module = createModule(name, dependencies, initFunc);

        // Initialize the module immediately if the core is already initialized
        if (api.initialized && api.supported) {
            module.init();
        }
    };

    api.createCoreModule = function(name, dependencies, initFunc) {
        createModule(name, dependencies, initFunc);
    };

    /*----------------------------------------------------------------------------------------------------------------*/

    // Ensure rangy.rangePrototype and rangy.selectionPrototype are available immediately

    function RangePrototype() {}
    api.RangePrototype = RangePrototype;
    api.rangePrototype = new RangePrototype();

    function SelectionPrototype() {}
    api.selectionPrototype = new SelectionPrototype();

    /*----------------------------------------------------------------------------------------------------------------*/

    // DOM utility methods used by Rangy
    api.createCoreModule("DomUtil", [], function(api, module) {
        var UNDEF = "undefined";
        var util = api.util;
        var getBody = util.getBody;

        // Perform feature tests
        if (!util.areHostMethods(document, ["createDocumentFragment", "createElement", "createTextNode"])) {
            module.fail("document missing a Node creation method");
        }

        if (!util.isHostMethod(document, "getElementsByTagName")) {
            module.fail("document missing getElementsByTagName method");
        }

        var el = document.createElement("div");
        if (!util.areHostMethods(el, ["insertBefore", "appendChild", "cloneNode"] ||
                !util.areHostObjects(el, ["previousSibling", "nextSibling", "childNodes", "parentNode"]))) {
            module.fail("Incomplete Element implementation");
        }

        // innerHTML is required for Range's createContextualFragment method
        if (!util.isHostProperty(el, "innerHTML")) {
            module.fail("Element is missing innerHTML property");
        }

        var textNode = document.createTextNode("test");
        if (!util.areHostMethods(textNode, ["splitText", "deleteData", "insertData", "appendData", "cloneNode"] ||
                !util.areHostObjects(el, ["previousSibling", "nextSibling", "childNodes", "parentNode"]) ||
                !util.areHostProperties(textNode, ["data"]))) {
            module.fail("Incomplete Text Node implementation");
        }

        /*----------------------------------------------------------------------------------------------------------------*/

        // Removed use of indexOf because of a bizarre bug in Opera that is thrown in one of the Acid3 tests. I haven't been
        // able to replicate it outside of the test. The bug is that indexOf returns -1 when called on an Array that
        // contains just the document as a single element and the value searched for is the document.
        var arrayContains = /*Array.prototype.indexOf ?
            function(arr, val) {
                return arr.indexOf(val) > -1;
            }:*/

            function(arr, val) {
                var i = arr.length;
                while (i--) {
                    if (arr[i] === val) {
                        return true;
                    }
                }
                return false;
            };

        // Opera 11 puts HTML elements in the null namespace, it seems, and IE 7 has undefined namespaceURI
        function isHtmlNamespace(node) {
            var ns;
            return typeof node.namespaceURI == UNDEF || ((ns = node.namespaceURI) === null || ns == "http://www.w3.org/1999/xhtml");
        }

        function parentElement(node) {
            var parent = node.parentNode;
            return (parent.nodeType == 1) ? parent : null;
        }

        function getNodeIndex(node) {
            var i = 0;
            while( (node = node.previousSibling) ) {
                ++i;
            }
            return i;
        }

        function getNodeLength(node) {
            switch (node.nodeType) {
                case 7:
                case 10:
                    return 0;
                case 3:
                case 8:
                    return node.length;
                default:
                    return node.childNodes.length;
            }
        }

        function getCommonAncestor(node1, node2) {
            var ancestors = [], n;
            for (n = node1; n; n = n.parentNode) {
                ancestors.push(n);
            }

            for (n = node2; n; n = n.parentNode) {
                if (arrayContains(ancestors, n)) {
                    return n;
                }
            }

            return null;
        }

        function isAncestorOf(ancestor, descendant, selfIsAncestor) {
            var n = selfIsAncestor ? descendant : descendant.parentNode;
            while (n) {
                if (n === ancestor) {
                    return true;
                } else {
                    n = n.parentNode;
                }
            }
            return false;
        }

        function isOrIsAncestorOf(ancestor, descendant) {
            return isAncestorOf(ancestor, descendant, true);
        }

        function getClosestAncestorIn(node, ancestor, selfIsAncestor) {
            var p, n = selfIsAncestor ? node : node.parentNode;
            while (n) {
                p = n.parentNode;
                if (p === ancestor) {
                    return n;
                }
                n = p;
            }
            return null;
        }

        function isCharacterDataNode(node) {
            var t = node.nodeType;
            return t == 3 || t == 4 || t == 8 ; // Text, CDataSection or Comment
        }

        function isTextOrCommentNode(node) {
            if (!node) {
                return false;
            }
            var t = node.nodeType;
            return t == 3 || t == 8 ; // Text or Comment
        }

        function insertAfter(node, precedingNode) {
            var nextNode = precedingNode.nextSibling, parent = precedingNode.parentNode;
            if (nextNode) {
                parent.insertBefore(node, nextNode);
            } else {
                parent.appendChild(node);
            }
            return node;
        }

        // Note that we cannot use splitText() because it is bugridden in IE 9.
        function splitDataNode(node, index, positionsToPreserve) {
            var newNode = node.cloneNode(false);
            newNode.deleteData(0, index);
            node.deleteData(index, node.length - index);
            insertAfter(newNode, node);

            // Preserve positions
            if (positionsToPreserve) {
                for (var i = 0, position; position = positionsToPreserve[i++]; ) {
                    // Handle case where position was inside the portion of node after the split point
                    if (position.node == node && position.offset > index) {
                        position.node = newNode;
                        position.offset -= index;
                    }
                    // Handle the case where the position is a node offset within node's parent
                    else if (position.node == node.parentNode && position.offset > getNodeIndex(node)) {
                        ++position.offset;
                    }
                }
            }
            return newNode;
        }

        function getDocument(node) {
            if (node.nodeType == 9) {
                return node;
            } else if (typeof node.ownerDocument != UNDEF) {
                return node.ownerDocument;
            } else if (typeof node.document != UNDEF) {
                return node.document;
            } else if (node.parentNode) {
                return getDocument(node.parentNode);
            } else {
                throw module.createError("getDocument: no document found for node");
            }
        }

        function getWindow(node) {
            var doc = getDocument(node);
            if (typeof doc.defaultView != UNDEF) {
                return doc.defaultView;
            } else if (typeof doc.parentWindow != UNDEF) {
                return doc.parentWindow;
            } else {
                throw module.createError("Cannot get a window object for node");
            }
        }

        function getIframeDocument(iframeEl) {
            if (typeof iframeEl.contentDocument != UNDEF) {
                return iframeEl.contentDocument;
            } else if (typeof iframeEl.contentWindow != UNDEF) {
                return iframeEl.contentWindow.document;
            } else {
                throw module.createError("getIframeDocument: No Document object found for iframe element");
            }
        }

        function getIframeWindow(iframeEl) {
            if (typeof iframeEl.contentWindow != UNDEF) {
                return iframeEl.contentWindow;
            } else if (typeof iframeEl.contentDocument != UNDEF) {
                return iframeEl.contentDocument.defaultView;
            } else {
                throw module.createError("getIframeWindow: No Window object found for iframe element");
            }
        }

        // This looks bad. Is it worth it?
        function isWindow(obj) {
            return obj && util.isHostMethod(obj, "setTimeout") && util.isHostObject(obj, "document");
        }

        function getContentDocument(obj, module, methodName) {
            var doc;

            if (!obj) {
                doc = document;
            }

            // Test if a DOM node has been passed and obtain a document object for it if so
            else if (util.isHostProperty(obj, "nodeType")) {
                doc = (obj.nodeType == 1 && obj.tagName.toLowerCase() == "iframe") ?
                    getIframeDocument(obj) : getDocument(obj);
            }

            // Test if the doc parameter appears to be a Window object
            else if (isWindow(obj)) {
                doc = obj.document;
            }

            if (!doc) {
                throw module.createError(methodName + "(): Parameter must be a Window object or DOM node");
            }

            return doc;
        }

        function getRootContainer(node) {
            var parent;
            while ( (parent = node.parentNode) ) {
                node = parent;
            }
            return node;
        }

        function comparePoints(nodeA, offsetA, nodeB, offsetB) {
            // See http://www.w3.org/TR/DOM-Level-2-Traversal-Range/ranges.html#Level-2-Range-Comparing
            var nodeC, root, childA, childB, n;
            if (nodeA == nodeB) {
                // Case 1: nodes are the same
                return offsetA === offsetB ? 0 : (offsetA < offsetB) ? -1 : 1;
            } else if ( (nodeC = getClosestAncestorIn(nodeB, nodeA, true)) ) {
                // Case 2: node C (container B or an ancestor) is a child node of A
                return offsetA <= getNodeIndex(nodeC) ? -1 : 1;
            } else if ( (nodeC = getClosestAncestorIn(nodeA, nodeB, true)) ) {
                // Case 3: node C (container A or an ancestor) is a child node of B
                return getNodeIndex(nodeC) < offsetB  ? -1 : 1;
            } else {
                root = getCommonAncestor(nodeA, nodeB);
                if (!root) {
                    throw new Error("comparePoints error: nodes have no common ancestor");
                }

                // Case 4: containers are siblings or descendants of siblings
                childA = (nodeA === root) ? root : getClosestAncestorIn(nodeA, root, true);
                childB = (nodeB === root) ? root : getClosestAncestorIn(nodeB, root, true);

                if (childA === childB) {
                    // This shouldn't be possible
                    throw module.createError("comparePoints got to case 4 and childA and childB are the same!");
                } else {
                    n = root.firstChild;
                    while (n) {
                        if (n === childA) {
                            return -1;
                        } else if (n === childB) {
                            return 1;
                        }
                        n = n.nextSibling;
                    }
                }
            }
        }

        /*----------------------------------------------------------------------------------------------------------------*/

        // Test for IE's crash (IE 6/7) or exception (IE >= 8) when a reference to garbage-collected text node is queried
        var crashyTextNodes = false;

        function isBrokenNode(node) {
            var n;
            try {
                n = node.parentNode;
                return false;
            } catch (e) {
                return true;
            }
        }

        (function() {
            var el = document.createElement("b");
            el.innerHTML = "1";
            var textNode = el.firstChild;
            el.innerHTML = "<br />";
            crashyTextNodes = isBrokenNode(textNode);

            api.features.crashyTextNodes = crashyTextNodes;
        })();

        /*----------------------------------------------------------------------------------------------------------------*/

        function inspectNode(node) {
            if (!node) {
                return "[No node]";
            }
            if (crashyTextNodes && isBrokenNode(node)) {
                return "[Broken node]";
            }
            if (isCharacterDataNode(node)) {
                return '"' + node.data + '"';
            }
            if (node.nodeType == 1) {
                var idAttr = node.id ? ' id="' + node.id + '"' : "";
                return "<" + node.nodeName + idAttr + ">[index:" + getNodeIndex(node) + ",length:" + node.childNodes.length + "][" + (node.innerHTML || "[innerHTML not supported]").slice(0, 25) + "]";
            }
            return node.nodeName;
        }

        function fragmentFromNodeChildren(node) {
            var fragment = getDocument(node).createDocumentFragment(), child;
            while ( (child = node.firstChild) ) {
                fragment.appendChild(child);
            }
            return fragment;
        }

        var getComputedStyleProperty;
        if (typeof window.getComputedStyle != UNDEF) {
            getComputedStyleProperty = function(el, propName) {
                return getWindow(el).getComputedStyle(el, null)[propName];
            };
        } else if (typeof document.documentElement.currentStyle != UNDEF) {
            getComputedStyleProperty = function(el, propName) {
                return el.currentStyle[propName];
            };
        } else {
            module.fail("No means of obtaining computed style properties found");
        }

        function createTestElement(doc, html, contentEditable) {
            var body = getBody(doc);
            var el = doc.createElement("div");
            el.contentEditable = "" + !!contentEditable;
            if (html) {
                el.innerHTML = html;
            }

            // Insert the test element at the start of the body to prevent scrolling to the bottom in iOS (issue #292)
            var bodyFirstChild = body.firstChild;
            if (bodyFirstChild) {
                body.insertBefore(el, bodyFirstChild);
            } else {
                body.appendChild(el);
            }

            return el;
        }

        function removeNode(node) {
            return node.parentNode.removeChild(node);
        }

        function NodeIterator(root) {
            this.root = root;
            this._next = root;
        }

        NodeIterator.prototype = {
            _current: null,

            hasNext: function() {
                return !!this._next;
            },

            next: function() {
                var n = this._current = this._next;
                var child, next;
                if (this._current) {
                    child = n.firstChild;
                    if (child) {
                        this._next = child;
                    } else {
                        next = null;
                        while ((n !== this.root) && !(next = n.nextSibling)) {
                            n = n.parentNode;
                        }
                        this._next = next;
                    }
                }
                return this._current;
            },

            detach: function() {
                this._current = this._next = this.root = null;
            }
        };

        function createIterator(root) {
            return new NodeIterator(root);
        }

        function DomPosition(node, offset) {
            this.node = node;
            this.offset = offset;
        }

        DomPosition.prototype = {
            equals: function(pos) {
                return !!pos && this.node === pos.node && this.offset == pos.offset;
            },

            inspect: function() {
                return "[DomPosition(" + inspectNode(this.node) + ":" + this.offset + ")]";
            },

            toString: function() {
                return this.inspect();
            }
        };

        function DOMException(codeName) {
            this.code = this[codeName];
            this.codeName = codeName;
            this.message = "DOMException: " + this.codeName;
        }

        DOMException.prototype = {
            INDEX_SIZE_ERR: 1,
            HIERARCHY_REQUEST_ERR: 3,
            WRONG_DOCUMENT_ERR: 4,
            NO_MODIFICATION_ALLOWED_ERR: 7,
            NOT_FOUND_ERR: 8,
            NOT_SUPPORTED_ERR: 9,
            INVALID_STATE_ERR: 11,
            INVALID_NODE_TYPE_ERR: 24
        };

        DOMException.prototype.toString = function() {
            return this.message;
        };

        api.dom = {
            arrayContains: arrayContains,
            isHtmlNamespace: isHtmlNamespace,
            parentElement: parentElement,
            getNodeIndex: getNodeIndex,
            getNodeLength: getNodeLength,
            getCommonAncestor: getCommonAncestor,
            isAncestorOf: isAncestorOf,
            isOrIsAncestorOf: isOrIsAncestorOf,
            getClosestAncestorIn: getClosestAncestorIn,
            isCharacterDataNode: isCharacterDataNode,
            isTextOrCommentNode: isTextOrCommentNode,
            insertAfter: insertAfter,
            splitDataNode: splitDataNode,
            getDocument: getDocument,
            getWindow: getWindow,
            getIframeWindow: getIframeWindow,
            getIframeDocument: getIframeDocument,
            getBody: getBody,
            isWindow: isWindow,
            getContentDocument: getContentDocument,
            getRootContainer: getRootContainer,
            comparePoints: comparePoints,
            isBrokenNode: isBrokenNode,
            inspectNode: inspectNode,
            getComputedStyleProperty: getComputedStyleProperty,
            createTestElement: createTestElement,
            removeNode: removeNode,
            fragmentFromNodeChildren: fragmentFromNodeChildren,
            createIterator: createIterator,
            DomPosition: DomPosition
        };

        api.DOMException = DOMException;
    });

    /*----------------------------------------------------------------------------------------------------------------*/

    // Pure JavaScript implementation of DOM Range
    api.createCoreModule("DomRange", ["DomUtil"], function(api, module) {
        var dom = api.dom;
        var util = api.util;
        var DomPosition = dom.DomPosition;
        var DOMException = api.DOMException;

        var isCharacterDataNode = dom.isCharacterDataNode;
        var getNodeIndex = dom.getNodeIndex;
        var isOrIsAncestorOf = dom.isOrIsAncestorOf;
        var getDocument = dom.getDocument;
        var comparePoints = dom.comparePoints;
        var splitDataNode = dom.splitDataNode;
        var getClosestAncestorIn = dom.getClosestAncestorIn;
        var getNodeLength = dom.getNodeLength;
        var arrayContains = dom.arrayContains;
        var getRootContainer = dom.getRootContainer;
        var crashyTextNodes = api.features.crashyTextNodes;

        var removeNode = dom.removeNode;

        /*----------------------------------------------------------------------------------------------------------------*/

        // Utility functions

        function isNonTextPartiallySelected(node, range) {
            return (node.nodeType != 3) &&
                   (isOrIsAncestorOf(node, range.startContainer) || isOrIsAncestorOf(node, range.endContainer));
        }

        function getRangeDocument(range) {
            return range.document || getDocument(range.startContainer);
        }

        function getBoundaryBeforeNode(node) {
            return new DomPosition(node.parentNode, getNodeIndex(node));
        }

        function getBoundaryAfterNode(node) {
            return new DomPosition(node.parentNode, getNodeIndex(node) + 1);
        }

        function insertNodeAtPosition(node, n, o) {
            var firstNodeInserted = node.nodeType == 11 ? node.firstChild : node;
            if (isCharacterDataNode(n)) {
                if (o == n.length) {
                    dom.insertAfter(node, n);
                } else {
                    n.parentNode.insertBefore(node, o == 0 ? n : splitDataNode(n, o));
                }
            } else if (o >= n.childNodes.length) {
                n.appendChild(node);
            } else {
                n.insertBefore(node, n.childNodes[o]);
            }
            return firstNodeInserted;
        }

        function rangesIntersect(rangeA, rangeB, touchingIsIntersecting) {
            assertRangeValid(rangeA);
            assertRangeValid(rangeB);

            if (getRangeDocument(rangeB) != getRangeDocument(rangeA)) {
                throw new DOMException("WRONG_DOCUMENT_ERR");
            }

            var startComparison = comparePoints(rangeA.startContainer, rangeA.startOffset, rangeB.endContainer, rangeB.endOffset),
                endComparison = comparePoints(rangeA.endContainer, rangeA.endOffset, rangeB.startContainer, rangeB.startOffset);

            return touchingIsIntersecting ? startComparison <= 0 && endComparison >= 0 : startComparison < 0 && endComparison > 0;
        }

        function cloneSubtree(iterator) {
            var partiallySelected;
            for (var node, frag = getRangeDocument(iterator.range).createDocumentFragment(), subIterator; node = iterator.next(); ) {
                partiallySelected = iterator.isPartiallySelectedSubtree();
                node = node.cloneNode(!partiallySelected);
                if (partiallySelected) {
                    subIterator = iterator.getSubtreeIterator();
                    node.appendChild(cloneSubtree(subIterator));
                    subIterator.detach();
                }

                if (node.nodeType == 10) { // DocumentType
                    throw new DOMException("HIERARCHY_REQUEST_ERR");
                }
                frag.appendChild(node);
            }
            return frag;
        }

        function iterateSubtree(rangeIterator, func, iteratorState) {
            var it, n;
            iteratorState = iteratorState || { stop: false };
            for (var node, subRangeIterator; node = rangeIterator.next(); ) {
                if (rangeIterator.isPartiallySelectedSubtree()) {
                    if (func(node) === false) {
                        iteratorState.stop = true;
                        return;
                    } else {
                        // The node is partially selected by the Range, so we can use a new RangeIterator on the portion of
                        // the node selected by the Range.
                        subRangeIterator = rangeIterator.getSubtreeIterator();
                        iterateSubtree(subRangeIterator, func, iteratorState);
                        subRangeIterator.detach();
                        if (iteratorState.stop) {
                            return;
                        }
                    }
                } else {
                    // The whole node is selected, so we can use efficient DOM iteration to iterate over the node and its
                    // descendants
                    it = dom.createIterator(node);
                    while ( (n = it.next()) ) {
                        if (func(n) === false) {
                            iteratorState.stop = true;
                            return;
                        }
                    }
                }
            }
        }

        function deleteSubtree(iterator) {
            var subIterator;
            while (iterator.next()) {
                if (iterator.isPartiallySelectedSubtree()) {
                    subIterator = iterator.getSubtreeIterator();
                    deleteSubtree(subIterator);
                    subIterator.detach();
                } else {
                    iterator.remove();
                }
            }
        }

        function extractSubtree(iterator) {
            for (var node, frag = getRangeDocument(iterator.range).createDocumentFragment(), subIterator; node = iterator.next(); ) {

                if (iterator.isPartiallySelectedSubtree()) {
                    node = node.cloneNode(false);
                    subIterator = iterator.getSubtreeIterator();
                    node.appendChild(extractSubtree(subIterator));
                    subIterator.detach();
                } else {
                    iterator.remove();
                }
                if (node.nodeType == 10) { // DocumentType
                    throw new DOMException("HIERARCHY_REQUEST_ERR");
                }
                frag.appendChild(node);
            }
            return frag;
        }

        function getNodesInRange(range, nodeTypes, filter) {
            var filterNodeTypes = !!(nodeTypes && nodeTypes.length), regex;
            var filterExists = !!filter;
            if (filterNodeTypes) {
                regex = new RegExp("^(" + nodeTypes.join("|") + ")$");
            }

            var nodes = [];
            iterateSubtree(new RangeIterator(range, false), function(node) {
                if (filterNodeTypes && !regex.test(node.nodeType)) {
                    return;
                }
                if (filterExists && !filter(node)) {
                    return;
                }
                // Don't include a boundary container if it is a character data node and the range does not contain any
                // of its character data. See issue 190.
                var sc = range.startContainer;
                if (node == sc && isCharacterDataNode(sc) && range.startOffset == sc.length) {
                    return;
                }

                var ec = range.endContainer;
                if (node == ec && isCharacterDataNode(ec) && range.endOffset == 0) {
                    return;
                }

                nodes.push(node);
            });
            return nodes;
        }

        function inspect(range) {
            var name = (typeof range.getName == "undefined") ? "Range" : range.getName();
            return "[" + name + "(" + dom.inspectNode(range.startContainer) + ":" + range.startOffset + ", " +
                    dom.inspectNode(range.endContainer) + ":" + range.endOffset + ")]";
        }

        /*----------------------------------------------------------------------------------------------------------------*/

        // RangeIterator code partially borrows from IERange by Tim Ryan (http://github.com/timcameronryan/IERange)

        function RangeIterator(range, clonePartiallySelectedTextNodes) {
            this.range = range;
            this.clonePartiallySelectedTextNodes = clonePartiallySelectedTextNodes;


            if (!range.collapsed) {
                this.sc = range.startContainer;
                this.so = range.startOffset;
                this.ec = range.endContainer;
                this.eo = range.endOffset;
                var root = range.commonAncestorContainer;

                if (this.sc === this.ec && isCharacterDataNode(this.sc)) {
                    this.isSingleCharacterDataNode = true;
                    this._first = this._last = this._next = this.sc;
                } else {
                    this._first = this._next = (this.sc === root && !isCharacterDataNode(this.sc)) ?
                        this.sc.childNodes[this.so] : getClosestAncestorIn(this.sc, root, true);
                    this._last = (this.ec === root && !isCharacterDataNode(this.ec)) ?
                        this.ec.childNodes[this.eo - 1] : getClosestAncestorIn(this.ec, root, true);
                }
            }
        }

        RangeIterator.prototype = {
            _current: null,
            _next: null,
            _first: null,
            _last: null,
            isSingleCharacterDataNode: false,

            reset: function() {
                this._current = null;
                this._next = this._first;
            },

            hasNext: function() {
                return !!this._next;
            },

            next: function() {
                // Move to next node
                var current = this._current = this._next;
                if (current) {
                    this._next = (current !== this._last) ? current.nextSibling : null;

                    // Check for partially selected text nodes
                    if (isCharacterDataNode(current) && this.clonePartiallySelectedTextNodes) {
                        if (current === this.ec) {
                            (current = current.cloneNode(true)).deleteData(this.eo, current.length - this.eo);
                        }
                        if (this._current === this.sc) {
                            (current = current.cloneNode(true)).deleteData(0, this.so);
                        }
                    }
                }

                return current;
            },

            remove: function() {
                var current = this._current, start, end;

                if (isCharacterDataNode(current) && (current === this.sc || current === this.ec)) {
                    start = (current === this.sc) ? this.so : 0;
                    end = (current === this.ec) ? this.eo : current.length;
                    if (start != end) {
                        current.deleteData(start, end - start);
                    }
                } else {
                    if (current.parentNode) {
                        removeNode(current);
                    } else {
                    }
                }
            },

            // Checks if the current node is partially selected
            isPartiallySelectedSubtree: function() {
                var current = this._current;
                return isNonTextPartiallySelected(current, this.range);
            },

            getSubtreeIterator: function() {
                var subRange;
                if (this.isSingleCharacterDataNode) {
                    subRange = this.range.cloneRange();
                    subRange.collapse(false);
                } else {
                    subRange = new Range(getRangeDocument(this.range));
                    var current = this._current;
                    var startContainer = current, startOffset = 0, endContainer = current, endOffset = getNodeLength(current);

                    if (isOrIsAncestorOf(current, this.sc)) {
                        startContainer = this.sc;
                        startOffset = this.so;
                    }
                    if (isOrIsAncestorOf(current, this.ec)) {
                        endContainer = this.ec;
                        endOffset = this.eo;
                    }

                    updateBoundaries(subRange, startContainer, startOffset, endContainer, endOffset);
                }
                return new RangeIterator(subRange, this.clonePartiallySelectedTextNodes);
            },

            detach: function() {
                this.range = this._current = this._next = this._first = this._last = this.sc = this.so = this.ec = this.eo = null;
            }
        };

        /*----------------------------------------------------------------------------------------------------------------*/

        var beforeAfterNodeTypes = [1, 3, 4, 5, 7, 8, 10];
        var rootContainerNodeTypes = [2, 9, 11];
        var readonlyNodeTypes = [5, 6, 10, 12];
        var insertableNodeTypes = [1, 3, 4, 5, 7, 8, 10, 11];
        var surroundNodeTypes = [1, 3, 4, 5, 7, 8];

        function createAncestorFinder(nodeTypes) {
            return function(node, selfIsAncestor) {
                var t, n = selfIsAncestor ? node : node.parentNode;
                while (n) {
                    t = n.nodeType;
                    if (arrayContains(nodeTypes, t)) {
                        return n;
                    }
                    n = n.parentNode;
                }
                return null;
            };
        }

        var getDocumentOrFragmentContainer = createAncestorFinder( [9, 11] );
        var getReadonlyAncestor = createAncestorFinder(readonlyNodeTypes);
        var getDocTypeNotationEntityAncestor = createAncestorFinder( [6, 10, 12] );

        function assertNoDocTypeNotationEntityAncestor(node, allowSelf) {
            if (getDocTypeNotationEntityAncestor(node, allowSelf)) {
                throw new DOMException("INVALID_NODE_TYPE_ERR");
            }
        }

        function assertValidNodeType(node, invalidTypes) {
            if (!arrayContains(invalidTypes, node.nodeType)) {
                throw new DOMException("INVALID_NODE_TYPE_ERR");
            }
        }

        function assertValidOffset(node, offset) {
            if (offset < 0 || offset > (isCharacterDataNode(node) ? node.length : node.childNodes.length)) {
                throw new DOMException("INDEX_SIZE_ERR");
            }
        }

        function assertSameDocumentOrFragment(node1, node2) {
            if (getDocumentOrFragmentContainer(node1, true) !== getDocumentOrFragmentContainer(node2, true)) {
                throw new DOMException("WRONG_DOCUMENT_ERR");
            }
        }

        function assertNodeNotReadOnly(node) {
            if (getReadonlyAncestor(node, true)) {
                throw new DOMException("NO_MODIFICATION_ALLOWED_ERR");
            }
        }

        function assertNode(node, codeName) {
            if (!node) {
                throw new DOMException(codeName);
            }
        }

        function isOrphan(node) {
            return (crashyTextNodes && dom.isBrokenNode(node)) ||
                !arrayContains(rootContainerNodeTypes, node.nodeType) && !getDocumentOrFragmentContainer(node, true);
        }

        function isValidOffset(node, offset) {
            return offset <= (isCharacterDataNode(node) ? node.length : node.childNodes.length);
        }

        function isRangeValid(range) {
            return (!!range.startContainer && !!range.endContainer &&
                    !isOrphan(range.startContainer) &&
                    !isOrphan(range.endContainer) &&
                    isValidOffset(range.startContainer, range.startOffset) &&
                    isValidOffset(range.endContainer, range.endOffset));
        }

        function assertRangeValid(range) {
            if (!isRangeValid(range)) {
                throw new Error("Range error: Range is no longer valid after DOM mutation (" + range.inspect() + ")");
            }
        }

        /*----------------------------------------------------------------------------------------------------------------*/

        // Test the browser's innerHTML support to decide how to implement createContextualFragment
        var styleEl = document.createElement("style");
        var htmlParsingConforms = false;
        try {
            styleEl.innerHTML = "<b>x</b>";
            htmlParsingConforms = (styleEl.firstChild.nodeType == 3); // Opera incorrectly creates an element node
        } catch (e) {
            // IE 6 and 7 throw
        }

        api.features.htmlParsingConforms = htmlParsingConforms;

        var createContextualFragment = htmlParsingConforms ?

            // Implementation as per HTML parsing spec, trusting in the browser's implementation of innerHTML. See
            // discussion and base code for this implementation at issue 67.
            // Spec: http://html5.org/specs/dom-parsing.html#extensions-to-the-range-interface
            // Thanks to Aleks Williams.
            function(fragmentStr) {
                // "Let node the context object's start's node."
                var node = this.startContainer;
                var doc = getDocument(node);

                // "If the context object's start's node is null, raise an INVALID_STATE_ERR
                // exception and abort these steps."
                if (!node) {
                    throw new DOMException("INVALID_STATE_ERR");
                }

                // "Let element be as follows, depending on node's interface:"
                // Document, Document Fragment: null
                var el = null;

                // "Element: node"
                if (node.nodeType == 1) {
                    el = node;

                // "Text, Comment: node's parentElement"
                } else if (isCharacterDataNode(node)) {
                    el = dom.parentElement(node);
                }

                // "If either element is null or element's ownerDocument is an HTML document
                // and element's local name is "html" and element's namespace is the HTML
                // namespace"
                if (el === null || (
                    el.nodeName == "HTML" &&
                    dom.isHtmlNamespace(getDocument(el).documentElement) &&
                    dom.isHtmlNamespace(el)
                )) {

                // "let element be a new Element with "body" as its local name and the HTML
                // namespace as its namespace.""
                    el = doc.createElement("body");
                } else {
                    el = el.cloneNode(false);
                }

                // "If the node's document is an HTML document: Invoke the HTML fragment parsing algorithm."
                // "If the node's document is an XML document: Invoke the XML fragment parsing algorithm."
                // "In either case, the algorithm must be invoked with fragment as the input
                // and element as the context element."
                el.innerHTML = fragmentStr;

                // "If this raises an exception, then abort these steps. Otherwise, let new
                // children be the nodes returned."

                // "Let fragment be a new DocumentFragment."
                // "Append all new children to fragment."
                // "Return fragment."
                return dom.fragmentFromNodeChildren(el);
            } :

            // In this case, innerHTML cannot be trusted, so fall back to a simpler, non-conformant implementation that
            // previous versions of Rangy used (with the exception of using a body element rather than a div)
            function(fragmentStr) {
                var doc = getRangeDocument(this);
                var el = doc.createElement("body");
                el.innerHTML = fragmentStr;

                return dom.fragmentFromNodeChildren(el);
            };

        function splitRangeBoundaries(range, positionsToPreserve) {
            assertRangeValid(range);

            var sc = range.startContainer, so = range.startOffset, ec = range.endContainer, eo = range.endOffset;
            var startEndSame = (sc === ec);

            if (isCharacterDataNode(ec) && eo > 0 && eo < ec.length) {
                splitDataNode(ec, eo, positionsToPreserve);
            }

            if (isCharacterDataNode(sc) && so > 0 && so < sc.length) {
                sc = splitDataNode(sc, so, positionsToPreserve);
                if (startEndSame) {
                    eo -= so;
                    ec = sc;
                } else if (ec == sc.parentNode && eo >= getNodeIndex(sc)) {
                    eo++;
                }
                so = 0;
            }
            range.setStartAndEnd(sc, so, ec, eo);
        }

        function rangeToHtml(range) {
            assertRangeValid(range);
            var container = range.commonAncestorContainer.parentNode.cloneNode(false);
            container.appendChild( range.cloneContents() );
            return container.innerHTML;
        }

        /*----------------------------------------------------------------------------------------------------------------*/

        var rangeProperties = ["startContainer", "startOffset", "endContainer", "endOffset", "collapsed",
            "commonAncestorContainer"];

        var s2s = 0, s2e = 1, e2e = 2, e2s = 3;
        var n_b = 0, n_a = 1, n_b_a = 2, n_i = 3;

        util.extend(api.rangePrototype, {
            compareBoundaryPoints: function(how, range) {
                assertRangeValid(this);
                assertSameDocumentOrFragment(this.startContainer, range.startContainer);

                var nodeA, offsetA, nodeB, offsetB;
                var prefixA = (how == e2s || how == s2s) ? "start" : "end";
                var prefixB = (how == s2e || how == s2s) ? "start" : "end";
                nodeA = this[prefixA + "Container"];
                offsetA = this[prefixA + "Offset"];
                nodeB = range[prefixB + "Container"];
                offsetB = range[prefixB + "Offset"];
                return comparePoints(nodeA, offsetA, nodeB, offsetB);
            },

            insertNode: function(node) {
                assertRangeValid(this);
                assertValidNodeType(node, insertableNodeTypes);
                assertNodeNotReadOnly(this.startContainer);

                if (isOrIsAncestorOf(node, this.startContainer)) {
                    throw new DOMException("HIERARCHY_REQUEST_ERR");
                }

                // No check for whether the container of the start of the Range is of a type that does not allow
                // children of the type of node: the browser's DOM implementation should do this for us when we attempt
                // to add the node

                var firstNodeInserted = insertNodeAtPosition(node, this.startContainer, this.startOffset);
                this.setStartBefore(firstNodeInserted);
            },

            cloneContents: function() {
                assertRangeValid(this);

                var clone, frag;
                if (this.collapsed) {
                    return getRangeDocument(this).createDocumentFragment();
                } else {
                    if (this.startContainer === this.endContainer && isCharacterDataNode(this.startContainer)) {
                        clone = this.startContainer.cloneNode(true);
                        clone.data = clone.data.slice(this.startOffset, this.endOffset);
                        frag = getRangeDocument(this).createDocumentFragment();
                        frag.appendChild(clone);
                        return frag;
                    } else {
                        var iterator = new RangeIterator(this, true);
                        clone = cloneSubtree(iterator);
                        iterator.detach();
                    }
                    return clone;
                }
            },

            canSurroundContents: function() {
                assertRangeValid(this);
                assertNodeNotReadOnly(this.startContainer);
                assertNodeNotReadOnly(this.endContainer);

                // Check if the contents can be surrounded. Specifically, this means whether the range partially selects
                // no non-text nodes.
                var iterator = new RangeIterator(this, true);
                var boundariesInvalid = (iterator._first && (isNonTextPartiallySelected(iterator._first, this)) ||
                        (iterator._last && isNonTextPartiallySelected(iterator._last, this)));
                iterator.detach();
                return !boundariesInvalid;
            },

            surroundContents: function(node) {
                assertValidNodeType(node, surroundNodeTypes);

                if (!this.canSurroundContents()) {
                    throw new DOMException("INVALID_STATE_ERR");
                }

                // Extract the contents
                var content = this.extractContents();

                // Clear the children of the node
                if (node.hasChildNodes()) {
                    while (node.lastChild) {
                        node.removeChild(node.lastChild);
                    }
                }

                // Insert the new node and add the extracted contents
                insertNodeAtPosition(node, this.startContainer, this.startOffset);
                node.appendChild(content);

                this.selectNode(node);
            },

            cloneRange: function() {
                assertRangeValid(this);
                var range = new Range(getRangeDocument(this));
                var i = rangeProperties.length, prop;
                while (i--) {
                    prop = rangeProperties[i];
                    range[prop] = this[prop];
                }
                return range;
            },

            toString: function() {
                assertRangeValid(this);
                var sc = this.startContainer;
                if (sc === this.endContainer && isCharacterDataNode(sc)) {
                    return (sc.nodeType == 3 || sc.nodeType == 4) ? sc.data.slice(this.startOffset, this.endOffset) : "";
                } else {
                    var textParts = [], iterator = new RangeIterator(this, true);
                    iterateSubtree(iterator, function(node) {
                        // Accept only text or CDATA nodes, not comments
                        if (node.nodeType == 3 || node.nodeType == 4) {
                            textParts.push(node.data);
                        }
                    });
                    iterator.detach();
                    return textParts.join("");
                }
            },

            // The methods below are all non-standard. The following batch were introduced by Mozilla but have since
            // been removed from Mozilla.

            compareNode: function(node) {
                assertRangeValid(this);

                var parent = node.parentNode;
                var nodeIndex = getNodeIndex(node);

                if (!parent) {
                    throw new DOMException("NOT_FOUND_ERR");
                }

                var startComparison = this.comparePoint(parent, nodeIndex),
                    endComparison = this.comparePoint(parent, nodeIndex + 1);

                if (startComparison < 0) { // Node starts before
                    return (endComparison > 0) ? n_b_a : n_b;
                } else {
                    return (endComparison > 0) ? n_a : n_i;
                }
            },

            comparePoint: function(node, offset) {
                assertRangeValid(this);
                assertNode(node, "HIERARCHY_REQUEST_ERR");
                assertSameDocumentOrFragment(node, this.startContainer);

                if (comparePoints(node, offset, this.startContainer, this.startOffset) < 0) {
                    return -1;
                } else if (comparePoints(node, offset, this.endContainer, this.endOffset) > 0) {
                    return 1;
                }
                return 0;
            },

            createContextualFragment: createContextualFragment,

            toHtml: function() {
                return rangeToHtml(this);
            },

            // touchingIsIntersecting determines whether this method considers a node that borders a range intersects
            // with it (as in WebKit) or not (as in Gecko pre-1.9, and the default)
            intersectsNode: function(node, touchingIsIntersecting) {
                assertRangeValid(this);
                assertNode(node, "NOT_FOUND_ERR");
                if (getDocument(node) !== getRangeDocument(this)) {
                    return false;
                }

                var parent = node.parentNode, offset = getNodeIndex(node);
                assertNode(parent, "NOT_FOUND_ERR");

                var startComparison = comparePoints(parent, offset, this.endContainer, this.endOffset),
                    endComparison = comparePoints(parent, offset + 1, this.startContainer, this.startOffset);

                return touchingIsIntersecting ? startComparison <= 0 && endComparison >= 0 : startComparison < 0 && endComparison > 0;
            },

            isPointInRange: function(node, offset) {
                assertRangeValid(this);
                assertNode(node, "HIERARCHY_REQUEST_ERR");
                assertSameDocumentOrFragment(node, this.startContainer);

                return (comparePoints(node, offset, this.startContainer, this.startOffset) >= 0) &&
                       (comparePoints(node, offset, this.endContainer, this.endOffset) <= 0);
            },

            // The methods below are non-standard and invented by me.

            // Sharing a boundary start-to-end or end-to-start does not count as intersection.
            intersectsRange: function(range) {
                return rangesIntersect(this, range, false);
            },

            // Sharing a boundary start-to-end or end-to-start does count as intersection.
            intersectsOrTouchesRange: function(range) {
                return rangesIntersect(this, range, true);
            },

            intersection: function(range) {
                if (this.intersectsRange(range)) {
                    var startComparison = comparePoints(this.startContainer, this.startOffset, range.startContainer, range.startOffset),
                        endComparison = comparePoints(this.endContainer, this.endOffset, range.endContainer, range.endOffset);

                    var intersectionRange = this.cloneRange();
                    if (startComparison == -1) {
                        intersectionRange.setStart(range.startContainer, range.startOffset);
                    }
                    if (endComparison == 1) {
                        intersectionRange.setEnd(range.endContainer, range.endOffset);
                    }
                    return intersectionRange;
                }
                return null;
            },

            union: function(range) {
                if (this.intersectsOrTouchesRange(range)) {
                    var unionRange = this.cloneRange();
                    if (comparePoints(range.startContainer, range.startOffset, this.startContainer, this.startOffset) == -1) {
                        unionRange.setStart(range.startContainer, range.startOffset);
                    }
                    if (comparePoints(range.endContainer, range.endOffset, this.endContainer, this.endOffset) == 1) {
                        unionRange.setEnd(range.endContainer, range.endOffset);
                    }
                    return unionRange;
                } else {
                    throw new DOMException("Ranges do not intersect");
                }
            },

            containsNode: function(node, allowPartial) {
                if (allowPartial) {
                    return this.intersectsNode(node, false);
                } else {
                    return this.compareNode(node) == n_i;
                }
            },

            containsNodeContents: function(node) {
                return this.comparePoint(node, 0) >= 0 && this.comparePoint(node, getNodeLength(node)) <= 0;
            },

            containsRange: function(range) {
                var intersection = this.intersection(range);
                return intersection !== null && range.equals(intersection);
            },

            containsNodeText: function(node) {
                var nodeRange = this.cloneRange();
                nodeRange.selectNode(node);
                var textNodes = nodeRange.getNodes([3]);
                if (textNodes.length > 0) {
                    nodeRange.setStart(textNodes[0], 0);
                    var lastTextNode = textNodes.pop();
                    nodeRange.setEnd(lastTextNode, lastTextNode.length);
                    return this.containsRange(nodeRange);
                } else {
                    return this.containsNodeContents(node);
                }
            },

            getNodes: function(nodeTypes, filter) {
                assertRangeValid(this);
                return getNodesInRange(this, nodeTypes, filter);
            },

            getDocument: function() {
                return getRangeDocument(this);
            },

            collapseBefore: function(node) {
                this.setEndBefore(node);
                this.collapse(false);
            },

            collapseAfter: function(node) {
                this.setStartAfter(node);
                this.collapse(true);
            },

            getBookmark: function(containerNode) {
                var doc = getRangeDocument(this);
                var preSelectionRange = api.createRange(doc);
                containerNode = containerNode || dom.getBody(doc);
                preSelectionRange.selectNodeContents(containerNode);
                var range = this.intersection(preSelectionRange);
                var start = 0, end = 0;
                if (range) {
                    preSelectionRange.setEnd(range.startContainer, range.startOffset);
                    start = preSelectionRange.toString().length;
                    end = start + range.toString().length;
                }

                return {
                    start: start,
                    end: end,
                    containerNode: containerNode
                };
            },

            moveToBookmark: function(bookmark) {
                var containerNode = bookmark.containerNode;
                var charIndex = 0;
                this.setStart(containerNode, 0);
                this.collapse(true);
                var nodeStack = [containerNode], node, foundStart = false, stop = false;
                var nextCharIndex, i, childNodes;

                while (!stop && (node = nodeStack.pop())) {
                    if (node.nodeType == 3) {
                        nextCharIndex = charIndex + node.length;
                        if (!foundStart && bookmark.start >= charIndex && bookmark.start <= nextCharIndex) {
                            this.setStart(node, bookmark.start - charIndex);
                            foundStart = true;
                        }
                        if (foundStart && bookmark.end >= charIndex && bookmark.end <= nextCharIndex) {
                            this.setEnd(node, bookmark.end - charIndex);
                            stop = true;
                        }
                        charIndex = nextCharIndex;
                    } else {
                        childNodes = node.childNodes;
                        i = childNodes.length;
                        while (i--) {
                            nodeStack.push(childNodes[i]);
                        }
                    }
                }
            },

            getName: function() {
                return "DomRange";
            },

            equals: function(range) {
                return Range.rangesEqual(this, range);
            },

            isValid: function() {
                return isRangeValid(this);
            },

            inspect: function() {
                return inspect(this);
            },

            detach: function() {
                // In DOM4, detach() is now a no-op.
            }
        });

        function copyComparisonConstantsToObject(obj) {
            obj.START_TO_START = s2s;
            obj.START_TO_END = s2e;
            obj.END_TO_END = e2e;
            obj.END_TO_START = e2s;

            obj.NODE_BEFORE = n_b;
            obj.NODE_AFTER = n_a;
            obj.NODE_BEFORE_AND_AFTER = n_b_a;
            obj.NODE_INSIDE = n_i;
        }

        function copyComparisonConstants(constructor) {
            copyComparisonConstantsToObject(constructor);
            copyComparisonConstantsToObject(constructor.prototype);
        }

        function createRangeContentRemover(remover, boundaryUpdater) {
            return function() {
                assertRangeValid(this);

                var sc = this.startContainer, so = this.startOffset, root = this.commonAncestorContainer;

                var iterator = new RangeIterator(this, true);

                // Work out where to position the range after content removal
                var node, boundary;
                if (sc !== root) {
                    node = getClosestAncestorIn(sc, root, true);
                    boundary = getBoundaryAfterNode(node);
                    sc = boundary.node;
                    so = boundary.offset;
                }

                // Check none of the range is read-only
                iterateSubtree(iterator, assertNodeNotReadOnly);

                iterator.reset();

                // Remove the content
                var returnValue = remover(iterator);
                iterator.detach();

                // Move to the new position
                boundaryUpdater(this, sc, so, sc, so);

                return returnValue;
            };
        }

        function createPrototypeRange(constructor, boundaryUpdater) {
            function createBeforeAfterNodeSetter(isBefore, isStart) {
                return function(node) {
                    assertValidNodeType(node, beforeAfterNodeTypes);
                    assertValidNodeType(getRootContainer(node), rootContainerNodeTypes);

                    var boundary = (isBefore ? getBoundaryBeforeNode : getBoundaryAfterNode)(node);
                    (isStart ? setRangeStart : setRangeEnd)(this, boundary.node, boundary.offset);
                };
            }

            function setRangeStart(range, node, offset) {
                var ec = range.endContainer, eo = range.endOffset;
                if (node !== range.startContainer || offset !== range.startOffset) {
                    // Check the root containers of the range and the new boundary, and also check whether the new boundary
                    // is after the current end. In either case, collapse the range to the new position
                    if (getRootContainer(node) != getRootContainer(ec) || comparePoints(node, offset, ec, eo) == 1) {
                        ec = node;
                        eo = offset;
                    }
                    boundaryUpdater(range, node, offset, ec, eo);
                }
            }

            function setRangeEnd(range, node, offset) {
                var sc = range.startContainer, so = range.startOffset;
                if (node !== range.endContainer || offset !== range.endOffset) {
                    // Check the root containers of the range and the new boundary, and also check whether the new boundary
                    // is after the current end. In either case, collapse the range to the new position
                    if (getRootContainer(node) != getRootContainer(sc) || comparePoints(node, offset, sc, so) == -1) {
                        sc = node;
                        so = offset;
                    }
                    boundaryUpdater(range, sc, so, node, offset);
                }
            }

            // Set up inheritance
            var F = function() {};
            F.prototype = api.rangePrototype;
            constructor.prototype = new F();

            util.extend(constructor.prototype, {
                setStart: function(node, offset) {
                    assertNoDocTypeNotationEntityAncestor(node, true);
                    assertValidOffset(node, offset);

                    setRangeStart(this, node, offset);
                },

                setEnd: function(node, offset) {
                    assertNoDocTypeNotationEntityAncestor(node, true);
                    assertValidOffset(node, offset);

                    setRangeEnd(this, node, offset);
                },

                /**
                 * Convenience method to set a range's start and end boundaries. Overloaded as follows:
                 * - Two parameters (node, offset) creates a collapsed range at that position
                 * - Three parameters (node, startOffset, endOffset) creates a range contained with node starting at
                 *   startOffset and ending at endOffset
                 * - Four parameters (startNode, startOffset, endNode, endOffset) creates a range starting at startOffset in
                 *   startNode and ending at endOffset in endNode
                 */
                setStartAndEnd: function() {
                    var args = arguments;
                    var sc = args[0], so = args[1], ec = sc, eo = so;

                    switch (args.length) {
                        case 3:
                            eo = args[2];
                            break;
                        case 4:
                            ec = args[2];
                            eo = args[3];
                            break;
                    }

                    boundaryUpdater(this, sc, so, ec, eo);
                },

                setBoundary: function(node, offset, isStart) {
                    this["set" + (isStart ? "Start" : "End")](node, offset);
                },

                setStartBefore: createBeforeAfterNodeSetter(true, true),
                setStartAfter: createBeforeAfterNodeSetter(false, true),
                setEndBefore: createBeforeAfterNodeSetter(true, false),
                setEndAfter: createBeforeAfterNodeSetter(false, false),

                collapse: function(isStart) {
                    assertRangeValid(this);
                    if (isStart) {
                        boundaryUpdater(this, this.startContainer, this.startOffset, this.startContainer, this.startOffset);
                    } else {
                        boundaryUpdater(this, this.endContainer, this.endOffset, this.endContainer, this.endOffset);
                    }
                },

                selectNodeContents: function(node) {
                    assertNoDocTypeNotationEntityAncestor(node, true);

                    boundaryUpdater(this, node, 0, node, getNodeLength(node));
                },

                selectNode: function(node) {
                    assertNoDocTypeNotationEntityAncestor(node, false);
                    assertValidNodeType(node, beforeAfterNodeTypes);

                    var start = getBoundaryBeforeNode(node), end = getBoundaryAfterNode(node);
                    boundaryUpdater(this, start.node, start.offset, end.node, end.offset);
                },

                extractContents: createRangeContentRemover(extractSubtree, boundaryUpdater),

                deleteContents: createRangeContentRemover(deleteSubtree, boundaryUpdater),

                canSurroundContents: function() {
                    assertRangeValid(this);
                    assertNodeNotReadOnly(this.startContainer);
                    assertNodeNotReadOnly(this.endContainer);

                    // Check if the contents can be surrounded. Specifically, this means whether the range partially selects
                    // no non-text nodes.
                    var iterator = new RangeIterator(this, true);
                    var boundariesInvalid = (iterator._first && isNonTextPartiallySelected(iterator._first, this) ||
                            (iterator._last && isNonTextPartiallySelected(iterator._last, this)));
                    iterator.detach();
                    return !boundariesInvalid;
                },

                splitBoundaries: function() {
                    splitRangeBoundaries(this);
                },

                splitBoundariesPreservingPositions: function(positionsToPreserve) {
                    splitRangeBoundaries(this, positionsToPreserve);
                },

                normalizeBoundaries: function() {
                    assertRangeValid(this);

                    var sc = this.startContainer, so = this.startOffset, ec = this.endContainer, eo = this.endOffset;

                    var mergeForward = function(node) {
                        var sibling = node.nextSibling;
                        if (sibling && sibling.nodeType == node.nodeType) {
                            ec = node;
                            eo = node.length;
                            node.appendData(sibling.data);
                            removeNode(sibling);
                        }
                    };

                    var mergeBackward = function(node) {
                        var sibling = node.previousSibling;
                        if (sibling && sibling.nodeType == node.nodeType) {
                            sc = node;
                            var nodeLength = node.length;
                            so = sibling.length;
                            node.insertData(0, sibling.data);
                            removeNode(sibling);
                            if (sc == ec) {
                                eo += so;
                                ec = sc;
                            } else if (ec == node.parentNode) {
                                var nodeIndex = getNodeIndex(node);
                                if (eo == nodeIndex) {
                                    ec = node;
                                    eo = nodeLength;
                                } else if (eo > nodeIndex) {
                                    eo--;
                                }
                            }
                        }
                    };

                    var normalizeStart = true;

                    if (isCharacterDataNode(ec)) {
                        if (ec.length == eo) {
                            mergeForward(ec);
                        }
                    } else {
                        if (eo > 0) {
                            var endNode = ec.childNodes[eo - 1];
                            if (endNode && isCharacterDataNode(endNode)) {
                                mergeForward(endNode);
                            }
                        }
                        normalizeStart = !this.collapsed;
                    }

                    if (normalizeStart) {
                        if (isCharacterDataNode(sc)) {
                            if (so == 0) {
                                mergeBackward(sc);
                            }
                        } else {
                            if (so < sc.childNodes.length) {
                                var startNode = sc.childNodes[so];
                                if (startNode && isCharacterDataNode(startNode)) {
                                    mergeBackward(startNode);
                                }
                            }
                        }
                    } else {
                        sc = ec;
                        so = eo;
                    }

                    boundaryUpdater(this, sc, so, ec, eo);
                },

                collapseToPoint: function(node, offset) {
                    assertNoDocTypeNotationEntityAncestor(node, true);
                    assertValidOffset(node, offset);
                    this.setStartAndEnd(node, offset);
                }
            });

            copyComparisonConstants(constructor);
        }

        /*----------------------------------------------------------------------------------------------------------------*/

        // Updates commonAncestorContainer and collapsed after boundary change
        function updateCollapsedAndCommonAncestor(range) {
            range.collapsed = (range.startContainer === range.endContainer && range.startOffset === range.endOffset);
            range.commonAncestorContainer = range.collapsed ?
                range.startContainer : dom.getCommonAncestor(range.startContainer, range.endContainer);
        }

        function updateBoundaries(range, startContainer, startOffset, endContainer, endOffset) {
            range.startContainer = startContainer;
            range.startOffset = startOffset;
            range.endContainer = endContainer;
            range.endOffset = endOffset;
            range.document = dom.getDocument(startContainer);

            updateCollapsedAndCommonAncestor(range);
        }

        function Range(doc) {
            this.startContainer = doc;
            this.startOffset = 0;
            this.endContainer = doc;
            this.endOffset = 0;
            this.document = doc;
            updateCollapsedAndCommonAncestor(this);
        }

        createPrototypeRange(Range, updateBoundaries);

        util.extend(Range, {
            rangeProperties: rangeProperties,
            RangeIterator: RangeIterator,
            copyComparisonConstants: copyComparisonConstants,
            createPrototypeRange: createPrototypeRange,
            inspect: inspect,
            toHtml: rangeToHtml,
            getRangeDocument: getRangeDocument,
            rangesEqual: function(r1, r2) {
                return r1.startContainer === r2.startContainer &&
                    r1.startOffset === r2.startOffset &&
                    r1.endContainer === r2.endContainer &&
                    r1.endOffset === r2.endOffset;
            }
        });

        api.DomRange = Range;
    });

    /*----------------------------------------------------------------------------------------------------------------*/

    // Wrappers for the browser's native DOM Range and/or TextRange implementation
    api.createCoreModule("WrappedRange", ["DomRange"], function(api, module) {
        var WrappedRange, WrappedTextRange;
        var dom = api.dom;
        var util = api.util;
        var DomPosition = dom.DomPosition;
        var DomRange = api.DomRange;
        var getBody = dom.getBody;
        var getContentDocument = dom.getContentDocument;
        var isCharacterDataNode = dom.isCharacterDataNode;


        /*----------------------------------------------------------------------------------------------------------------*/

        if (api.features.implementsDomRange) {
            // This is a wrapper around the browser's native DOM Range. It has two aims:
            // - Provide workarounds for specific browser bugs
            // - provide convenient extensions, which are inherited from Rangy's DomRange

            (function() {
                var rangeProto;
                var rangeProperties = DomRange.rangeProperties;

                function updateRangeProperties(range) {
                    var i = rangeProperties.length, prop;
                    while (i--) {
                        prop = rangeProperties[i];
                        range[prop] = range.nativeRange[prop];
                    }
                    // Fix for broken collapsed property in IE 9.
                    range.collapsed = (range.startContainer === range.endContainer && range.startOffset === range.endOffset);
                }

                function updateNativeRange(range, startContainer, startOffset, endContainer, endOffset) {
                    var startMoved = (range.startContainer !== startContainer || range.startOffset != startOffset);
                    var endMoved = (range.endContainer !== endContainer || range.endOffset != endOffset);
                    var nativeRangeDifferent = !range.equals(range.nativeRange);

                    // Always set both boundaries for the benefit of IE9 (see issue 35)
                    if (startMoved || endMoved || nativeRangeDifferent) {
                        range.setEnd(endContainer, endOffset);
                        range.setStart(startContainer, startOffset);
                    }
                }

                var createBeforeAfterNodeSetter;

                WrappedRange = function(range) {
                    if (!range) {
                        throw module.createError("WrappedRange: Range must be specified");
                    }
                    this.nativeRange = range;
                    updateRangeProperties(this);
                };

                DomRange.createPrototypeRange(WrappedRange, updateNativeRange);

                rangeProto = WrappedRange.prototype;

                rangeProto.selectNode = function(node) {
                    this.nativeRange.selectNode(node);
                    updateRangeProperties(this);
                };

                rangeProto.cloneContents = function() {
                    return this.nativeRange.cloneContents();
                };

                // Due to a long-standing Firefox bug that I have not been able to find a reliable way to detect,
                // insertNode() is never delegated to the native range.

                rangeProto.surroundContents = function(node) {
                    this.nativeRange.surroundContents(node);
                    updateRangeProperties(this);
                };

                rangeProto.collapse = function(isStart) {
                    this.nativeRange.collapse(isStart);
                    updateRangeProperties(this);
                };

                rangeProto.cloneRange = function() {
                    return new WrappedRange(this.nativeRange.cloneRange());
                };

                rangeProto.refresh = function() {
                    updateRangeProperties(this);
                };

                rangeProto.toString = function() {
                    return this.nativeRange.toString();
                };

                // Create test range and node for feature detection

                var testTextNode = document.createTextNode("test");
                getBody(document).appendChild(testTextNode);
                var range = document.createRange();

                /*--------------------------------------------------------------------------------------------------------*/

                // Test for Firefox 2 bug that prevents moving the start of a Range to a point after its current end and
                // correct for it

                range.setStart(testTextNode, 0);
                range.setEnd(testTextNode, 0);

                try {
                    range.setStart(testTextNode, 1);

                    rangeProto.setStart = function(node, offset) {
                        this.nativeRange.setStart(node, offset);
                        updateRangeProperties(this);
                    };

                    rangeProto.setEnd = function(node, offset) {
                        this.nativeRange.setEnd(node, offset);
                        updateRangeProperties(this);
                    };

                    createBeforeAfterNodeSetter = function(name) {
                        return function(node) {
                            this.nativeRange[name](node);
                            updateRangeProperties(this);
                        };
                    };

                } catch(ex) {

                    rangeProto.setStart = function(node, offset) {
                        try {
                            this.nativeRange.setStart(node, offset);
                        } catch (ex) {
                            this.nativeRange.setEnd(node, offset);
                            this.nativeRange.setStart(node, offset);
                        }
                        updateRangeProperties(this);
                    };

                    rangeProto.setEnd = function(node, offset) {
                        try {
                            this.nativeRange.setEnd(node, offset);
                        } catch (ex) {
                            this.nativeRange.setStart(node, offset);
                            this.nativeRange.setEnd(node, offset);
                        }
                        updateRangeProperties(this);
                    };

                    createBeforeAfterNodeSetter = function(name, oppositeName) {
                        return function(node) {
                            try {
                                this.nativeRange[name](node);
                            } catch (ex) {
                                this.nativeRange[oppositeName](node);
                                this.nativeRange[name](node);
                            }
                            updateRangeProperties(this);
                        };
                    };
                }

                rangeProto.setStartBefore = createBeforeAfterNodeSetter("setStartBefore", "setEndBefore");
                rangeProto.setStartAfter = createBeforeAfterNodeSetter("setStartAfter", "setEndAfter");
                rangeProto.setEndBefore = createBeforeAfterNodeSetter("setEndBefore", "setStartBefore");
                rangeProto.setEndAfter = createBeforeAfterNodeSetter("setEndAfter", "setStartAfter");

                /*--------------------------------------------------------------------------------------------------------*/

                // Always use DOM4-compliant selectNodeContents implementation: it's simpler and less code than testing
                // whether the native implementation can be trusted
                rangeProto.selectNodeContents = function(node) {
                    this.setStartAndEnd(node, 0, dom.getNodeLength(node));
                };

                /*--------------------------------------------------------------------------------------------------------*/

                // Test for and correct WebKit bug that has the behaviour of compareBoundaryPoints round the wrong way for
                // constants START_TO_END and END_TO_START: https://bugs.webkit.org/show_bug.cgi?id=20738

                range.selectNodeContents(testTextNode);
                range.setEnd(testTextNode, 3);

                var range2 = document.createRange();
                range2.selectNodeContents(testTextNode);
                range2.setEnd(testTextNode, 4);
                range2.setStart(testTextNode, 2);

                if (range.compareBoundaryPoints(range.START_TO_END, range2) == -1 &&
                        range.compareBoundaryPoints(range.END_TO_START, range2) == 1) {
                    // This is the wrong way round, so correct for it

                    rangeProto.compareBoundaryPoints = function(type, range) {
                        range = range.nativeRange || range;
                        if (type == range.START_TO_END) {
                            type = range.END_TO_START;
                        } else if (type == range.END_TO_START) {
                            type = range.START_TO_END;
                        }
                        return this.nativeRange.compareBoundaryPoints(type, range);
                    };
                } else {
                    rangeProto.compareBoundaryPoints = function(type, range) {
                        return this.nativeRange.compareBoundaryPoints(type, range.nativeRange || range);
                    };
                }

                /*--------------------------------------------------------------------------------------------------------*/

                // Test for IE deleteContents() and extractContents() bug and correct it. See issue 107.

                var el = document.createElement("div");
                el.innerHTML = "123";
                var textNode = el.firstChild;
                var body = getBody(document);
                body.appendChild(el);

                range.setStart(textNode, 1);
                range.setEnd(textNode, 2);
                range.deleteContents();

                if (textNode.data == "13") {
                    // Behaviour is correct per DOM4 Range so wrap the browser's implementation of deleteContents() and
                    // extractContents()
                    rangeProto.deleteContents = function() {
                        this.nativeRange.deleteContents();
                        updateRangeProperties(this);
                    };

                    rangeProto.extractContents = function() {
                        var frag = this.nativeRange.extractContents();
                        updateRangeProperties(this);
                        return frag;
                    };
                } else {
                }

                body.removeChild(el);
                body = null;

                /*--------------------------------------------------------------------------------------------------------*/

                // Test for existence of createContextualFragment and delegate to it if it exists
                if (util.isHostMethod(range, "createContextualFragment")) {
                    rangeProto.createContextualFragment = function(fragmentStr) {
                        return this.nativeRange.createContextualFragment(fragmentStr);
                    };
                }

                /*--------------------------------------------------------------------------------------------------------*/

                // Clean up
                getBody(document).removeChild(testTextNode);

                rangeProto.getName = function() {
                    return "WrappedRange";
                };

                api.WrappedRange = WrappedRange;

                api.createNativeRange = function(doc) {
                    doc = getContentDocument(doc, module, "createNativeRange");
                    return doc.createRange();
                };
            })();
        }

        if (api.features.implementsTextRange) {
            /*
            This is a workaround for a bug where IE returns the wrong container element from the TextRange's parentElement()
            method. For example, in the following (where pipes denote the selection boundaries):

            <ul id="ul"><li id="a">| a </li><li id="b"> b |</li></ul>

            var range = document.selection.createRange();
            alert(range.parentElement().id); // Should alert "ul" but alerts "b"

            This method returns the common ancestor node of the following:
            - the parentElement() of the textRange
            - the parentElement() of the textRange after calling collapse(true)
            - the parentElement() of the textRange after calling collapse(false)
            */
            var getTextRangeContainerElement = function(textRange) {
                var parentEl = textRange.parentElement();
                var range = textRange.duplicate();
                range.collapse(true);
                var startEl = range.parentElement();
                range = textRange.duplicate();
                range.collapse(false);
                var endEl = range.parentElement();
                var startEndContainer = (startEl == endEl) ? startEl : dom.getCommonAncestor(startEl, endEl);

                return startEndContainer == parentEl ? startEndContainer : dom.getCommonAncestor(parentEl, startEndContainer);
            };

            var textRangeIsCollapsed = function(textRange) {
                return textRange.compareEndPoints("StartToEnd", textRange) == 0;
            };

            // Gets the boundary of a TextRange expressed as a node and an offset within that node. This function started
            // out as an improved version of code found in Tim Cameron Ryan's IERange (http://code.google.com/p/ierange/)
            // but has grown, fixing problems with line breaks in preformatted text, adding workaround for IE TextRange
            // bugs, handling for inputs and images, plus optimizations.
            var getTextRangeBoundaryPosition = function(textRange, wholeRangeContainerElement, isStart, isCollapsed, startInfo) {
                var workingRange = textRange.duplicate();
                workingRange.collapse(isStart);
                var containerElement = workingRange.parentElement();

                // Sometimes collapsing a TextRange that's at the start of a text node can move it into the previous node, so
                // check for that
                if (!dom.isOrIsAncestorOf(wholeRangeContainerElement, containerElement)) {
                    containerElement = wholeRangeContainerElement;
                }


                // Deal with nodes that cannot "contain rich HTML markup". In practice, this means form inputs, images and
                // similar. See http://msdn.microsoft.com/en-us/library/aa703950%28VS.85%29.aspx
                if (!containerElement.canHaveHTML) {
                    var pos = new DomPosition(containerElement.parentNode, dom.getNodeIndex(containerElement));
                    return {
                        boundaryPosition: pos,
                        nodeInfo: {
                            nodeIndex: pos.offset,
                            containerElement: pos.node
                        }
                    };
                }

                var workingNode = dom.getDocument(containerElement).createElement("span");

                // Workaround for HTML5 Shiv's insane violation of document.createElement(). See Rangy issue 104 and HTML5
                // Shiv issue 64: https://github.com/aFarkas/html5shiv/issues/64
                if (workingNode.parentNode) {
                    dom.removeNode(workingNode);
                }

                var comparison, workingComparisonType = isStart ? "StartToStart" : "StartToEnd";
                var previousNode, nextNode, boundaryPosition, boundaryNode;
                var start = (startInfo && startInfo.containerElement == containerElement) ? startInfo.nodeIndex : 0;
                var childNodeCount = containerElement.childNodes.length;
                var end = childNodeCount;

                // Check end first. Code within the loop assumes that the endth child node of the container is definitely
                // after the range boundary.
                var nodeIndex = end;

                while (true) {
                    if (nodeIndex == childNodeCount) {
                        containerElement.appendChild(workingNode);
                    } else {
                        containerElement.insertBefore(workingNode, containerElement.childNodes[nodeIndex]);
                    }
                    workingRange.moveToElementText(workingNode);
                    comparison = workingRange.compareEndPoints(workingComparisonType, textRange);
                    if (comparison == 0 || start == end) {
                        break;
                    } else if (comparison == -1) {
                        if (end == start + 1) {
                            // We know the endth child node is after the range boundary, so we must be done.
                            break;
                        } else {
                            start = nodeIndex;
                        }
                    } else {
                        end = (end == start + 1) ? start : nodeIndex;
                    }
                    nodeIndex = Math.floor((start + end) / 2);
                    containerElement.removeChild(workingNode);
                }


                // We've now reached or gone past the boundary of the text range we're interested in
                // so have identified the node we want
                boundaryNode = workingNode.nextSibling;

                if (comparison == -1 && boundaryNode && isCharacterDataNode(boundaryNode)) {
                    // This is a character data node (text, comment, cdata). The working range is collapsed at the start of
                    // the node containing the text range's boundary, so we move the end of the working range to the
                    // boundary point and measure the length of its text to get the boundary's offset within the node.
                    workingRange.setEndPoint(isStart ? "EndToStart" : "EndToEnd", textRange);

                    var offset;

                    if (/[\r\n]/.test(boundaryNode.data)) {
                        /*
                        For the particular case of a boundary within a text node containing rendered line breaks (within a
                        <pre> element, for example), we need a slightly complicated approach to get the boundary's offset in
                        IE. The facts:

                        - Each line break is represented as \r in the text node's data/nodeValue properties
                        - Each line break is represented as \r\n in the TextRange's 'text' property
                        - The 'text' property of the TextRange does not contain trailing line breaks

                        To get round the problem presented by the final fact above, we can use the fact that TextRange's
                        moveStart() and moveEnd() methods return the actual number of characters moved, which is not
                        necessarily the same as the number of characters it was instructed to move. The simplest approach is
                        to use this to store the characters moved when moving both the start and end of the range to the
                        start of the document body and subtracting the start offset from the end offset (the
                        "move-negative-gazillion" method). However, this is extremely slow when the document is large and
                        the range is near the end of it. Clearly doing the mirror image (i.e. moving the range boundaries to
                        the end of the document) has the same problem.

                        Another approach that works is to use moveStart() to move the start boundary of the range up to the
                        end boundary one character at a time and incrementing a counter with the value returned by the
                        moveStart() call. However, the check for whether the start boundary has reached the end boundary is
                        expensive, so this method is slow (although unlike "move-negative-gazillion" is largely unaffected
                        by the location of the range within the document).

                        The approach used below is a hybrid of the two methods above. It uses the fact that a string
                        containing the TextRange's 'text' property with each \r\n converted to a single \r character cannot
                        be longer than the text of the TextRange, so the start of the range is moved that length initially
                        and then a character at a time to make up for any trailing line breaks not contained in the 'text'
                        property. This has good performance in most situations compared to the previous two methods.
                        */
                        var tempRange = workingRange.duplicate();
                        var rangeLength = tempRange.text.replace(/\r\n/g, "\r").length;

                        offset = tempRange.moveStart("character", rangeLength);
                        while ( (comparison = tempRange.compareEndPoints("StartToEnd", tempRange)) == -1) {
                            offset++;
                            tempRange.moveStart("character", 1);
                        }
                    } else {
                        offset = workingRange.text.length;
                    }
                    boundaryPosition = new DomPosition(boundaryNode, offset);
                } else {

                    // If the boundary immediately follows a character data node and this is the end boundary, we should favour
                    // a position within that, and likewise for a start boundary preceding a character data node
                    previousNode = (isCollapsed || !isStart) && workingNode.previousSibling;
                    nextNode = (isCollapsed || isStart) && workingNode.nextSibling;
                    if (nextNode && isCharacterDataNode(nextNode)) {
                        boundaryPosition = new DomPosition(nextNode, 0);
                    } else if (previousNode && isCharacterDataNode(previousNode)) {
                        boundaryPosition = new DomPosition(previousNode, previousNode.data.length);
                    } else {
                        boundaryPosition = new DomPosition(containerElement, dom.getNodeIndex(workingNode));
                    }
                }

                // Clean up
                dom.removeNode(workingNode);

                return {
                    boundaryPosition: boundaryPosition,
                    nodeInfo: {
                        nodeIndex: nodeIndex,
                        containerElement: containerElement
                    }
                };
            };

            // Returns a TextRange representing the boundary of a TextRange expressed as a node and an offset within that
            // node. This function started out as an optimized version of code found in Tim Cameron Ryan's IERange
            // (http://code.google.com/p/ierange/)
            var createBoundaryTextRange = function(boundaryPosition, isStart) {
                var boundaryNode, boundaryParent, boundaryOffset = boundaryPosition.offset;
                var doc = dom.getDocument(boundaryPosition.node);
                var workingNode, childNodes, workingRange = getBody(doc).createTextRange();
                var nodeIsDataNode = isCharacterDataNode(boundaryPosition.node);

                if (nodeIsDataNode) {
                    boundaryNode = boundaryPosition.node;
                    boundaryParent = boundaryNode.parentNode;
                } else {
                    childNodes = boundaryPosition.node.childNodes;
                    boundaryNode = (boundaryOffset < childNodes.length) ? childNodes[boundaryOffset] : null;
                    boundaryParent = boundaryPosition.node;
                }

                // Position the range immediately before the node containing the boundary
                workingNode = doc.createElement("span");

                // Making the working element non-empty element persuades IE to consider the TextRange boundary to be within
                // the element rather than immediately before or after it
                workingNode.innerHTML = "&#feff;";

                // insertBefore is supposed to work like appendChild if the second parameter is null. However, a bug report
                // for IERange suggests that it can crash the browser: http://code.google.com/p/ierange/issues/detail?id=12
                if (boundaryNode) {
                    boundaryParent.insertBefore(workingNode, boundaryNode);
                } else {
                    boundaryParent.appendChild(workingNode);
                }

                workingRange.moveToElementText(workingNode);
                workingRange.collapse(!isStart);

                // Clean up
                boundaryParent.removeChild(workingNode);

                // Move the working range to the text offset, if required
                if (nodeIsDataNode) {
                    workingRange[isStart ? "moveStart" : "moveEnd"]("character", boundaryOffset);
                }

                return workingRange;
            };

            /*------------------------------------------------------------------------------------------------------------*/

            // This is a wrapper around a TextRange, providing full DOM Range functionality using rangy's DomRange as a
            // prototype

            WrappedTextRange = function(textRange) {
                this.textRange = textRange;
                this.refresh();
            };

            WrappedTextRange.prototype = new DomRange(document);

            WrappedTextRange.prototype.refresh = function() {
                var start, end, startBoundary;

                // TextRange's parentElement() method cannot be trusted. getTextRangeContainerElement() works around that.
                var rangeContainerElement = getTextRangeContainerElement(this.textRange);

                if (textRangeIsCollapsed(this.textRange)) {
                    end = start = getTextRangeBoundaryPosition(this.textRange, rangeContainerElement, true,
                        true).boundaryPosition;
                } else {
                    startBoundary = getTextRangeBoundaryPosition(this.textRange, rangeContainerElement, true, false);
                    start = startBoundary.boundaryPosition;

                    // An optimization used here is that if the start and end boundaries have the same parent element, the
                    // search scope for the end boundary can be limited to exclude the portion of the element that precedes
                    // the start boundary
                    end = getTextRangeBoundaryPosition(this.textRange, rangeContainerElement, false, false,
                        startBoundary.nodeInfo).boundaryPosition;
                }

                this.setStart(start.node, start.offset);
                this.setEnd(end.node, end.offset);
            };

            WrappedTextRange.prototype.getName = function() {
                return "WrappedTextRange";
            };

            DomRange.copyComparisonConstants(WrappedTextRange);

            var rangeToTextRange = function(range) {
                if (range.collapsed) {
                    return createBoundaryTextRange(new DomPosition(range.startContainer, range.startOffset), true);
                } else {
                    var startRange = createBoundaryTextRange(new DomPosition(range.startContainer, range.startOffset), true);
                    var endRange = createBoundaryTextRange(new DomPosition(range.endContainer, range.endOffset), false);
                    var textRange = getBody( DomRange.getRangeDocument(range) ).createTextRange();
                    textRange.setEndPoint("StartToStart", startRange);
                    textRange.setEndPoint("EndToEnd", endRange);
                    return textRange;
                }
            };

            WrappedTextRange.rangeToTextRange = rangeToTextRange;

            WrappedTextRange.prototype.toTextRange = function() {
                return rangeToTextRange(this);
            };

            api.WrappedTextRange = WrappedTextRange;

            // IE 9 and above have both implementations and Rangy makes both available. The next few lines sets which
            // implementation to use by default.
            if (!api.features.implementsDomRange || api.config.preferTextRange) {
                // Add WrappedTextRange as the Range property of the global object to allow expression like Range.END_TO_END to work
                var globalObj = (function(f) { return f("return this;")(); })(Function);
                if (typeof globalObj.Range == "undefined") {
                    globalObj.Range = WrappedTextRange;
                }

                api.createNativeRange = function(doc) {
                    doc = getContentDocument(doc, module, "createNativeRange");
                    return getBody(doc).createTextRange();
                };

                api.WrappedRange = WrappedTextRange;
            }
        }

        api.createRange = function(doc) {
            doc = getContentDocument(doc, module, "createRange");
            return new api.WrappedRange(api.createNativeRange(doc));
        };

        api.createRangyRange = function(doc) {
            doc = getContentDocument(doc, module, "createRangyRange");
            return new DomRange(doc);
        };

        api.createIframeRange = function(iframeEl) {
            module.deprecationNotice("createIframeRange()", "createRange(iframeEl)");
            return api.createRange(iframeEl);
        };

        api.createIframeRangyRange = function(iframeEl) {
            module.deprecationNotice("createIframeRangyRange()", "createRangyRange(iframeEl)");
            return api.createRangyRange(iframeEl);
        };

        api.addShimListener(function(win) {
            var doc = win.document;
            if (typeof doc.createRange == "undefined") {
                doc.createRange = function() {
                    return api.createRange(doc);
                };
            }
            doc = win = null;
        });
    });

    /*----------------------------------------------------------------------------------------------------------------*/

    // This module creates a selection object wrapper that conforms as closely as possible to the Selection specification
    // in the HTML Editing spec (http://dvcs.w3.org/hg/editing/raw-file/tip/editing.html#selections)
    api.createCoreModule("WrappedSelection", ["DomRange", "WrappedRange"], function(api, module) {
        api.config.checkSelectionRanges = true;

        var BOOLEAN = "boolean";
        var NUMBER = "number";
        var dom = api.dom;
        var util = api.util;
        var isHostMethod = util.isHostMethod;
        var DomRange = api.DomRange;
        var WrappedRange = api.WrappedRange;
        var DOMException = api.DOMException;
        var DomPosition = dom.DomPosition;
        var getNativeSelection;
        var selectionIsCollapsed;
        var features = api.features;
        var CONTROL = "Control";
        var getDocument = dom.getDocument;
        var getBody = dom.getBody;
        var rangesEqual = DomRange.rangesEqual;


        // Utility function to support direction parameters in the API that may be a string ("backward" or "forward") or a
        // Boolean (true for backwards).
        function isDirectionBackward(dir) {
            return (typeof dir == "string") ? /^backward(s)?$/i.test(dir) : !!dir;
        }

        function getWindow(win, methodName) {
            if (!win) {
                return window;
            } else if (dom.isWindow(win)) {
                return win;
            } else if (win instanceof WrappedSelection) {
                return win.win;
            } else {
                var doc = dom.getContentDocument(win, module, methodName);
                return dom.getWindow(doc);
            }
        }

        function getWinSelection(winParam) {
            return getWindow(winParam, "getWinSelection").getSelection();
        }

        function getDocSelection(winParam) {
            return getWindow(winParam, "getDocSelection").document.selection;
        }

        function winSelectionIsBackward(sel) {
            var backward = false;
            if (sel.anchorNode) {
                backward = (dom.comparePoints(sel.anchorNode, sel.anchorOffset, sel.focusNode, sel.focusOffset) == 1);
            }
            return backward;
        }

        // Test for the Range/TextRange and Selection features required
        // Test for ability to retrieve selection
        var implementsWinGetSelection = isHostMethod(window, "getSelection"),
            implementsDocSelection = util.isHostObject(document, "selection");

        features.implementsWinGetSelection = implementsWinGetSelection;
        features.implementsDocSelection = implementsDocSelection;

        var useDocumentSelection = implementsDocSelection && (!implementsWinGetSelection || api.config.preferTextRange);

        if (useDocumentSelection) {
            getNativeSelection = getDocSelection;
            api.isSelectionValid = function(winParam) {
                var doc = getWindow(winParam, "isSelectionValid").document, nativeSel = doc.selection;

                // Check whether the selection TextRange is actually contained within the correct document
                return (nativeSel.type != "None" || getDocument(nativeSel.createRange().parentElement()) == doc);
            };
        } else if (implementsWinGetSelection) {
            getNativeSelection = getWinSelection;
            api.isSelectionValid = function() {
                return true;
            };
        } else {
            module.fail("Neither document.selection or window.getSelection() detected.");
            return false;
        }

        api.getNativeSelection = getNativeSelection;

        var testSelection = getNativeSelection();

        // In Firefox, the selection is null in an iframe with display: none. See issue #138.
        if (!testSelection) {
            module.fail("Native selection was null (possibly issue 138?)");
            return false;
        }

        var testRange = api.createNativeRange(document);
        var body = getBody(document);

        // Obtaining a range from a selection
        var selectionHasAnchorAndFocus = util.areHostProperties(testSelection,
            ["anchorNode", "focusNode", "anchorOffset", "focusOffset"]);

        features.selectionHasAnchorAndFocus = selectionHasAnchorAndFocus;

        // Test for existence of native selection extend() method
        var selectionHasExtend = isHostMethod(testSelection, "extend");
        features.selectionHasExtend = selectionHasExtend;

        // Test if rangeCount exists
        var selectionHasRangeCount = (typeof testSelection.rangeCount == NUMBER);
        features.selectionHasRangeCount = selectionHasRangeCount;

        var selectionSupportsMultipleRanges = false;
        var collapsedNonEditableSelectionsSupported = true;

        var addRangeBackwardToNative = selectionHasExtend ?
            function(nativeSelection, range) {
                var doc = DomRange.getRangeDocument(range);
                var endRange = api.createRange(doc);
                endRange.collapseToPoint(range.endContainer, range.endOffset);
                nativeSelection.addRange(getNativeRange(endRange));
                nativeSelection.extend(range.startContainer, range.startOffset);
            } : null;

        if (util.areHostMethods(testSelection, ["addRange", "getRangeAt", "removeAllRanges"]) &&
                typeof testSelection.rangeCount == NUMBER && features.implementsDomRange) {

            (function() {
                // Previously an iframe was used but this caused problems in some circumstances in IE, so tests are
                // performed on the current document's selection. See issue 109.

                // Note also that if a selection previously existed, it is wiped and later restored by these tests. This
                // will result in the selection direction begin reversed if the original selection was backwards and the
                // browser does not support setting backwards selections (Internet Explorer, I'm looking at you).
                var sel = window.getSelection();
                if (sel) {
                    // Store the current selection
                    var originalSelectionRangeCount = sel.rangeCount;
                    var selectionHasMultipleRanges = (originalSelectionRangeCount > 1);
                    var originalSelectionRanges = [];
                    var originalSelectionBackward = winSelectionIsBackward(sel);
                    for (var i = 0; i < originalSelectionRangeCount; ++i) {
                        originalSelectionRanges[i] = sel.getRangeAt(i);
                    }

                    // Create some test elements
                    var testEl = dom.createTestElement(document, "", false);
                    var textNode = testEl.appendChild( document.createTextNode("\u00a0\u00a0\u00a0") );

                    // Test whether the native selection will allow a collapsed selection within a non-editable element
                    var r1 = document.createRange();

                    r1.setStart(textNode, 1);
                    r1.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(r1);
                    collapsedNonEditableSelectionsSupported = (sel.rangeCount == 1);
                    sel.removeAllRanges();

                    // Test whether the native selection is capable of supporting multiple ranges.
                    if (!selectionHasMultipleRanges) {
                        // Doing the original feature test here in Chrome 36 (and presumably later versions) prints a
                        // console error of "Discontiguous selection is not supported." that cannot be suppressed. There's
                        // nothing we can do about this while retaining the feature test so we have to resort to a browser
                        // sniff. I'm not happy about it. See
                        // https://code.google.com/p/chromium/issues/detail?id=399791
                        var chromeMatch = window.navigator.appVersion.match(/Chrome\/(.*?) /);
                        if (chromeMatch && parseInt(chromeMatch[1]) >= 36) {
                            selectionSupportsMultipleRanges = false;
                        } else {
                            var r2 = r1.cloneRange();
                            r1.setStart(textNode, 0);
                            r2.setEnd(textNode, 3);
                            r2.setStart(textNode, 2);
                            sel.addRange(r1);
                            sel.addRange(r2);
                            selectionSupportsMultipleRanges = (sel.rangeCount == 2);
                        }
                    }

                    // Clean up
                    dom.removeNode(testEl);
                    sel.removeAllRanges();

                    for (i = 0; i < originalSelectionRangeCount; ++i) {
                        if (i == 0 && originalSelectionBackward) {
                            if (addRangeBackwardToNative) {
                                addRangeBackwardToNative(sel, originalSelectionRanges[i]);
                            } else {
                                api.warn("Rangy initialization: original selection was backwards but selection has been restored forwards because the browser does not support Selection.extend");
                                sel.addRange(originalSelectionRanges[i]);
                            }
                        } else {
                            sel.addRange(originalSelectionRanges[i]);
                        }
                    }
                }
            })();
        }

        features.selectionSupportsMultipleRanges = selectionSupportsMultipleRanges;
        features.collapsedNonEditableSelectionsSupported = collapsedNonEditableSelectionsSupported;

        // ControlRanges
        var implementsControlRange = false, testControlRange;

        if (body && isHostMethod(body, "createControlRange")) {
            testControlRange = body.createControlRange();
            if (util.areHostProperties(testControlRange, ["item", "add"])) {
                implementsControlRange = true;
            }
        }
        features.implementsControlRange = implementsControlRange;

        // Selection collapsedness
        if (selectionHasAnchorAndFocus) {
            selectionIsCollapsed = function(sel) {
                return sel.anchorNode === sel.focusNode && sel.anchorOffset === sel.focusOffset;
            };
        } else {
            selectionIsCollapsed = function(sel) {
                return sel.rangeCount ? sel.getRangeAt(sel.rangeCount - 1).collapsed : false;
            };
        }

        function updateAnchorAndFocusFromRange(sel, range, backward) {
            var anchorPrefix = backward ? "end" : "start", focusPrefix = backward ? "start" : "end";
            sel.anchorNode = range[anchorPrefix + "Container"];
            sel.anchorOffset = range[anchorPrefix + "Offset"];
            sel.focusNode = range[focusPrefix + "Container"];
            sel.focusOffset = range[focusPrefix + "Offset"];
        }

        function updateAnchorAndFocusFromNativeSelection(sel) {
            var nativeSel = sel.nativeSelection;
            sel.anchorNode = nativeSel.anchorNode;
            sel.anchorOffset = nativeSel.anchorOffset;
            sel.focusNode = nativeSel.focusNode;
            sel.focusOffset = nativeSel.focusOffset;
        }

        function updateEmptySelection(sel) {
            sel.anchorNode = sel.focusNode = null;
            sel.anchorOffset = sel.focusOffset = 0;
            sel.rangeCount = 0;
            sel.isCollapsed = true;
            sel._ranges.length = 0;
        }

        function getNativeRange(range) {
            var nativeRange;
            if (range instanceof DomRange) {
                nativeRange = api.createNativeRange(range.getDocument());
                nativeRange.setEnd(range.endContainer, range.endOffset);
                nativeRange.setStart(range.startContainer, range.startOffset);
            } else if (range instanceof WrappedRange) {
                nativeRange = range.nativeRange;
            } else if (features.implementsDomRange && (range instanceof dom.getWindow(range.startContainer).Range)) {
                nativeRange = range;
            }
            return nativeRange;
        }

        function rangeContainsSingleElement(rangeNodes) {
            if (!rangeNodes.length || rangeNodes[0].nodeType != 1) {
                return false;
            }
            for (var i = 1, len = rangeNodes.length; i < len; ++i) {
                if (!dom.isAncestorOf(rangeNodes[0], rangeNodes[i])) {
                    return false;
                }
            }
            return true;
        }

        function getSingleElementFromRange(range) {
            var nodes = range.getNodes();
            if (!rangeContainsSingleElement(nodes)) {
                throw module.createError("getSingleElementFromRange: range " + range.inspect() + " did not consist of a single element");
            }
            return nodes[0];
        }

        // Simple, quick test which only needs to distinguish between a TextRange and a ControlRange
        function isTextRange(range) {
            return !!range && typeof range.text != "undefined";
        }

        function updateFromTextRange(sel, range) {
            // Create a Range from the selected TextRange
            var wrappedRange = new WrappedRange(range);
            sel._ranges = [wrappedRange];

            updateAnchorAndFocusFromRange(sel, wrappedRange, false);
            sel.rangeCount = 1;
            sel.isCollapsed = wrappedRange.collapsed;
        }

        function updateControlSelection(sel) {
            // Update the wrapped selection based on what's now in the native selection
            sel._ranges.length = 0;
            if (sel.docSelection.type == "None") {
                updateEmptySelection(sel);
            } else {
                var controlRange = sel.docSelection.createRange();
                if (isTextRange(controlRange)) {
                    // This case (where the selection type is "Control" and calling createRange() on the selection returns
                    // a TextRange) can happen in IE 9. It happens, for example, when all elements in the selected
                    // ControlRange have been removed from the ControlRange and removed from the document.
                    updateFromTextRange(sel, controlRange);
                } else {
                    sel.rangeCount = controlRange.length;
                    var range, doc = getDocument(controlRange.item(0));
                    for (var i = 0; i < sel.rangeCount; ++i) {
                        range = api.createRange(doc);
                        range.selectNode(controlRange.item(i));
                        sel._ranges.push(range);
                    }
                    sel.isCollapsed = sel.rangeCount == 1 && sel._ranges[0].collapsed;
                    updateAnchorAndFocusFromRange(sel, sel._ranges[sel.rangeCount - 1], false);
                }
            }
        }

        function addRangeToControlSelection(sel, range) {
            var controlRange = sel.docSelection.createRange();
            var rangeElement = getSingleElementFromRange(range);

            // Create a new ControlRange containing all the elements in the selected ControlRange plus the element
            // contained by the supplied range
            var doc = getDocument(controlRange.item(0));
            var newControlRange = getBody(doc).createControlRange();
            for (var i = 0, len = controlRange.length; i < len; ++i) {
                newControlRange.add(controlRange.item(i));
            }
            try {
                newControlRange.add(rangeElement);
            } catch (ex) {
                throw module.createError("addRange(): Element within the specified Range could not be added to control selection (does it have layout?)");
            }
            newControlRange.select();

            // Update the wrapped selection based on what's now in the native selection
            updateControlSelection(sel);
        }

        var getSelectionRangeAt;

        if (isHostMethod(testSelection, "getRangeAt")) {
            // try/catch is present because getRangeAt() must have thrown an error in some browser and some situation.
            // Unfortunately, I didn't write a comment about the specifics and am now scared to take it out. Let that be a
            // lesson to us all, especially me.
            getSelectionRangeAt = function(sel, index) {
                try {
                    return sel.getRangeAt(index);
                } catch (ex) {
                    return null;
                }
            };
        } else if (selectionHasAnchorAndFocus) {
            getSelectionRangeAt = function(sel) {
                var doc = getDocument(sel.anchorNode);
                var range = api.createRange(doc);
                range.setStartAndEnd(sel.anchorNode, sel.anchorOffset, sel.focusNode, sel.focusOffset);

                // Handle the case when the selection was selected backwards (from the end to the start in the
                // document)
                if (range.collapsed !== this.isCollapsed) {
                    range.setStartAndEnd(sel.focusNode, sel.focusOffset, sel.anchorNode, sel.anchorOffset);
                }

                return range;
            };
        }

        function WrappedSelection(selection, docSelection, win) {
            this.nativeSelection = selection;
            this.docSelection = docSelection;
            this._ranges = [];
            this.win = win;
            this.refresh();
        }

        WrappedSelection.prototype = api.selectionPrototype;

        function deleteProperties(sel) {
            sel.win = sel.anchorNode = sel.focusNode = sel._ranges = null;
            sel.rangeCount = sel.anchorOffset = sel.focusOffset = 0;
            sel.detached = true;
        }

        var cachedRangySelections = [];

        function actOnCachedSelection(win, action) {
            var i = cachedRangySelections.length, cached, sel;
            while (i--) {
                cached = cachedRangySelections[i];
                sel = cached.selection;
                if (action == "deleteAll") {
                    deleteProperties(sel);
                } else if (cached.win == win) {
                    if (action == "delete") {
                        cachedRangySelections.splice(i, 1);
                        return true;
                    } else {
                        return sel;
                    }
                }
            }
            if (action == "deleteAll") {
                cachedRangySelections.length = 0;
            }
            return null;
        }

        var getSelection = function(win) {
            // Check if the parameter is a Rangy Selection object
            if (win && win instanceof WrappedSelection) {
                win.refresh();
                return win;
            }

            win = getWindow(win, "getNativeSelection");

            var sel = actOnCachedSelection(win);
            var nativeSel = getNativeSelection(win), docSel = implementsDocSelection ? getDocSelection(win) : null;
            if (sel) {
                sel.nativeSelection = nativeSel;
                sel.docSelection = docSel;
                sel.refresh();
            } else {
                sel = new WrappedSelection(nativeSel, docSel, win);
                cachedRangySelections.push( { win: win, selection: sel } );
            }
            return sel;
        };

        api.getSelection = getSelection;

        api.getIframeSelection = function(iframeEl) {
            module.deprecationNotice("getIframeSelection()", "getSelection(iframeEl)");
            return api.getSelection(dom.getIframeWindow(iframeEl));
        };

        var selProto = WrappedSelection.prototype;

        function createControlSelection(sel, ranges) {
            // Ensure that the selection becomes of type "Control"
            var doc = getDocument(ranges[0].startContainer);
            var controlRange = getBody(doc).createControlRange();
            for (var i = 0, el, len = ranges.length; i < len; ++i) {
                el = getSingleElementFromRange(ranges[i]);
                try {
                    controlRange.add(el);
                } catch (ex) {
                    throw module.createError("setRanges(): Element within one of the specified Ranges could not be added to control selection (does it have layout?)");
                }
            }
            controlRange.select();

            // Update the wrapped selection based on what's now in the native selection
            updateControlSelection(sel);
        }

        // Selecting a range
        if (!useDocumentSelection && selectionHasAnchorAndFocus && util.areHostMethods(testSelection, ["removeAllRanges", "addRange"])) {
            selProto.removeAllRanges = function() {
                this.nativeSelection.removeAllRanges();
                updateEmptySelection(this);
            };

            var addRangeBackward = function(sel, range) {
                addRangeBackwardToNative(sel.nativeSelection, range);
                sel.refresh();
            };

            if (selectionHasRangeCount) {
                selProto.addRange = function(range, direction) {
                    if (implementsControlRange && implementsDocSelection && this.docSelection.type == CONTROL) {
                        addRangeToControlSelection(this, range);
                    } else {
                        if (isDirectionBackward(direction) && selectionHasExtend) {
                            addRangeBackward(this, range);
                        } else {
                            var previousRangeCount;
                            if (selectionSupportsMultipleRanges) {
                                previousRangeCount = this.rangeCount;
                            } else {
                                this.removeAllRanges();
                                previousRangeCount = 0;
                            }
                            // Clone the native range so that changing the selected range does not affect the selection.
                            // This is contrary to the spec but is the only way to achieve consistency between browsers. See
                            // issue 80.
                            var clonedNativeRange = getNativeRange(range).cloneRange();
                            try {
                                this.nativeSelection.addRange(clonedNativeRange);
                            } catch (ex) {
                            }

                            // Check whether adding the range was successful
                            this.rangeCount = this.nativeSelection.rangeCount;

                            if (this.rangeCount == previousRangeCount + 1) {
                                // The range was added successfully

                                // Check whether the range that we added to the selection is reflected in the last range extracted from
                                // the selection
                                if (api.config.checkSelectionRanges) {
                                    var nativeRange = getSelectionRangeAt(this.nativeSelection, this.rangeCount - 1);
                                    if (nativeRange && !rangesEqual(nativeRange, range)) {
                                        // Happens in WebKit with, for example, a selection placed at the start of a text node
                                        range = new WrappedRange(nativeRange);
                                    }
                                }
                                this._ranges[this.rangeCount - 1] = range;
                                updateAnchorAndFocusFromRange(this, range, selectionIsBackward(this.nativeSelection));
                                this.isCollapsed = selectionIsCollapsed(this);
                            } else {
                                // The range was not added successfully. The simplest thing is to refresh
                                this.refresh();
                            }
                        }
                    }
                };
            } else {
                selProto.addRange = function(range, direction) {
                    if (isDirectionBackward(direction) && selectionHasExtend) {
                        addRangeBackward(this, range);
                    } else {
                        this.nativeSelection.addRange(getNativeRange(range));
                        this.refresh();
                    }
                };
            }

            selProto.setRanges = function(ranges) {
                if (implementsControlRange && implementsDocSelection && ranges.length > 1) {
                    createControlSelection(this, ranges);
                } else {
                    this.removeAllRanges();
                    for (var i = 0, len = ranges.length; i < len; ++i) {
                        this.addRange(ranges[i]);
                    }
                }
            };
        } else if (isHostMethod(testSelection, "empty") && isHostMethod(testRange, "select") &&
                   implementsControlRange && useDocumentSelection) {

            selProto.removeAllRanges = function() {
                // Added try/catch as fix for issue #21
                try {
                    this.docSelection.empty();

                    // Check for empty() not working (issue #24)
                    if (this.docSelection.type != "None") {
                        // Work around failure to empty a control selection by instead selecting a TextRange and then
                        // calling empty()
                        var doc;
                        if (this.anchorNode) {
                            doc = getDocument(this.anchorNode);
                        } else if (this.docSelection.type == CONTROL) {
                            var controlRange = this.docSelection.createRange();
                            if (controlRange.length) {
                                doc = getDocument( controlRange.item(0) );
                            }
                        }
                        if (doc) {
                            var textRange = getBody(doc).createTextRange();
                            textRange.select();
                            this.docSelection.empty();
                        }
                    }
                } catch(ex) {}
                updateEmptySelection(this);
            };

            selProto.addRange = function(range) {
                if (this.docSelection.type == CONTROL) {
                    addRangeToControlSelection(this, range);
                } else {
                    api.WrappedTextRange.rangeToTextRange(range).select();
                    this._ranges[0] = range;
                    this.rangeCount = 1;
                    this.isCollapsed = this._ranges[0].collapsed;
                    updateAnchorAndFocusFromRange(this, range, false);
                }
            };

            selProto.setRanges = function(ranges) {
                this.removeAllRanges();
                var rangeCount = ranges.length;
                if (rangeCount > 1) {
                    createControlSelection(this, ranges);
                } else if (rangeCount) {
                    this.addRange(ranges[0]);
                }
            };
        } else {
            module.fail("No means of selecting a Range or TextRange was found");
            return false;
        }

        selProto.getRangeAt = function(index) {
            if (index < 0 || index >= this.rangeCount) {
                throw new DOMException("INDEX_SIZE_ERR");
            } else {
                // Clone the range to preserve selection-range independence. See issue 80.
                return this._ranges[index].cloneRange();
            }
        };

        var refreshSelection;

        if (useDocumentSelection) {
            refreshSelection = function(sel) {
                var range;
                if (api.isSelectionValid(sel.win)) {
                    range = sel.docSelection.createRange();
                } else {
                    range = getBody(sel.win.document).createTextRange();
                    range.collapse(true);
                }

                if (sel.docSelection.type == CONTROL) {
                    updateControlSelection(sel);
                } else if (isTextRange(range)) {
                    updateFromTextRange(sel, range);
                } else {
                    updateEmptySelection(sel);
                }
            };
        } else if (isHostMethod(testSelection, "getRangeAt") && typeof testSelection.rangeCount == NUMBER) {
            refreshSelection = function(sel) {
                if (implementsControlRange && implementsDocSelection && sel.docSelection.type == CONTROL) {
                    updateControlSelection(sel);
                } else {
                    sel._ranges.length = sel.rangeCount = sel.nativeSelection.rangeCount;
                    if (sel.rangeCount) {
                        for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                            sel._ranges[i] = new api.WrappedRange(sel.nativeSelection.getRangeAt(i));
                        }
                        updateAnchorAndFocusFromRange(sel, sel._ranges[sel.rangeCount - 1], selectionIsBackward(sel.nativeSelection));
                        sel.isCollapsed = selectionIsCollapsed(sel);
                    } else {
                        updateEmptySelection(sel);
                    }
                }
            };
        } else if (selectionHasAnchorAndFocus && typeof testSelection.isCollapsed == BOOLEAN && typeof testRange.collapsed == BOOLEAN && features.implementsDomRange) {
            refreshSelection = function(sel) {
                var range, nativeSel = sel.nativeSelection;
                if (nativeSel.anchorNode) {
                    range = getSelectionRangeAt(nativeSel, 0);
                    sel._ranges = [range];
                    sel.rangeCount = 1;
                    updateAnchorAndFocusFromNativeSelection(sel);
                    sel.isCollapsed = selectionIsCollapsed(sel);
                } else {
                    updateEmptySelection(sel);
                }
            };
        } else {
            module.fail("No means of obtaining a Range or TextRange from the user's selection was found");
            return false;
        }

        selProto.refresh = function(checkForChanges) {
            var oldRanges = checkForChanges ? this._ranges.slice(0) : null;
            var oldAnchorNode = this.anchorNode, oldAnchorOffset = this.anchorOffset;

            refreshSelection(this);
            if (checkForChanges) {
                // Check the range count first
                var i = oldRanges.length;
                if (i != this._ranges.length) {
                    return true;
                }

                // Now check the direction. Checking the anchor position is the same is enough since we're checking all the
                // ranges after this
                if (this.anchorNode != oldAnchorNode || this.anchorOffset != oldAnchorOffset) {
                    return true;
                }

                // Finally, compare each range in turn
                while (i--) {
                    if (!rangesEqual(oldRanges[i], this._ranges[i])) {
                        return true;
                    }
                }
                return false;
            }
        };

        // Removal of a single range
        var removeRangeManually = function(sel, range) {
            var ranges = sel.getAllRanges();
            sel.removeAllRanges();
            for (var i = 0, len = ranges.length; i < len; ++i) {
                if (!rangesEqual(range, ranges[i])) {
                    sel.addRange(ranges[i]);
                }
            }
            if (!sel.rangeCount) {
                updateEmptySelection(sel);
            }
        };

        if (implementsControlRange && implementsDocSelection) {
            selProto.removeRange = function(range) {
                if (this.docSelection.type == CONTROL) {
                    var controlRange = this.docSelection.createRange();
                    var rangeElement = getSingleElementFromRange(range);

                    // Create a new ControlRange containing all the elements in the selected ControlRange minus the
                    // element contained by the supplied range
                    var doc = getDocument(controlRange.item(0));
                    var newControlRange = getBody(doc).createControlRange();
                    var el, removed = false;
                    for (var i = 0, len = controlRange.length; i < len; ++i) {
                        el = controlRange.item(i);
                        if (el !== rangeElement || removed) {
                            newControlRange.add(controlRange.item(i));
                        } else {
                            removed = true;
                        }
                    }
                    newControlRange.select();

                    // Update the wrapped selection based on what's now in the native selection
                    updateControlSelection(this);
                } else {
                    removeRangeManually(this, range);
                }
            };
        } else {
            selProto.removeRange = function(range) {
                removeRangeManually(this, range);
            };
        }

        // Detecting if a selection is backward
        var selectionIsBackward;
        if (!useDocumentSelection && selectionHasAnchorAndFocus && features.implementsDomRange) {
            selectionIsBackward = winSelectionIsBackward;

            selProto.isBackward = function() {
                return selectionIsBackward(this);
            };
        } else {
            selectionIsBackward = selProto.isBackward = function() {
                return false;
            };
        }

        // Create an alias for backwards compatibility. From 1.3, everything is "backward" rather than "backwards"
        selProto.isBackwards = selProto.isBackward;

        // Selection stringifier
        // This is conformant to the old HTML5 selections draft spec but differs from WebKit and Mozilla's implementation.
        // The current spec does not yet define this method.
        selProto.toString = function() {
            var rangeTexts = [];
            for (var i = 0, len = this.rangeCount; i < len; ++i) {
                rangeTexts[i] = "" + this._ranges[i];
            }
            return rangeTexts.join("");
        };

        function assertNodeInSameDocument(sel, node) {
            if (sel.win.document != getDocument(node)) {
                throw new DOMException("WRONG_DOCUMENT_ERR");
            }
        }

        // No current browser conforms fully to the spec for this method, so Rangy's own method is always used
        selProto.collapse = function(node, offset) {
            assertNodeInSameDocument(this, node);
            var range = api.createRange(node);
            range.collapseToPoint(node, offset);
            this.setSingleRange(range);
            this.isCollapsed = true;
        };

        selProto.collapseToStart = function() {
            if (this.rangeCount) {
                var range = this._ranges[0];
                this.collapse(range.startContainer, range.startOffset);
            } else {
                throw new DOMException("INVALID_STATE_ERR");
            }
        };

        selProto.collapseToEnd = function() {
            if (this.rangeCount) {
                var range = this._ranges[this.rangeCount - 1];
                this.collapse(range.endContainer, range.endOffset);
            } else {
                throw new DOMException("INVALID_STATE_ERR");
            }
        };

        // The spec is very specific on how selectAllChildren should be implemented and not all browsers implement it as
        // specified so the native implementation is never used by Rangy.
        selProto.selectAllChildren = function(node) {
            assertNodeInSameDocument(this, node);
            var range = api.createRange(node);
            range.selectNodeContents(node);
            this.setSingleRange(range);
        };

        selProto.deleteFromDocument = function() {
            // Sepcial behaviour required for IE's control selections
            if (implementsControlRange && implementsDocSelection && this.docSelection.type == CONTROL) {
                var controlRange = this.docSelection.createRange();
                var element;
                while (controlRange.length) {
                    element = controlRange.item(0);
                    controlRange.remove(element);
                    dom.removeNode(element);
                }
                this.refresh();
            } else if (this.rangeCount) {
                var ranges = this.getAllRanges();
                if (ranges.length) {
                    this.removeAllRanges();
                    for (var i = 0, len = ranges.length; i < len; ++i) {
                        ranges[i].deleteContents();
                    }
                    // The spec says nothing about what the selection should contain after calling deleteContents on each
                    // range. Firefox moves the selection to where the final selected range was, so we emulate that
                    this.addRange(ranges[len - 1]);
                }
            }
        };

        // The following are non-standard extensions
        selProto.eachRange = function(func, returnValue) {
            for (var i = 0, len = this._ranges.length; i < len; ++i) {
                if ( func( this.getRangeAt(i) ) ) {
                    return returnValue;
                }
            }
        };

        selProto.getAllRanges = function() {
            var ranges = [];
            this.eachRange(function(range) {
                ranges.push(range);
            });
            return ranges;
        };

        selProto.setSingleRange = function(range, direction) {
            this.removeAllRanges();
            this.addRange(range, direction);
        };

        selProto.callMethodOnEachRange = function(methodName, params) {
            var results = [];
            this.eachRange( function(range) {
                results.push( range[methodName].apply(range, params) );
            } );
            return results;
        };

        function createStartOrEndSetter(isStart) {
            return function(node, offset) {
                var range;
                if (this.rangeCount) {
                    range = this.getRangeAt(0);
                    range["set" + (isStart ? "Start" : "End")](node, offset);
                } else {
                    range = api.createRange(this.win.document);
                    range.setStartAndEnd(node, offset);
                }
                this.setSingleRange(range, this.isBackward());
            };
        }

        selProto.setStart = createStartOrEndSetter(true);
        selProto.setEnd = createStartOrEndSetter(false);

        // Add select() method to Range prototype. Any existing selection will be removed.
        api.rangePrototype.select = function(direction) {
            getSelection( this.getDocument() ).setSingleRange(this, direction);
        };

        selProto.changeEachRange = function(func) {
            var ranges = [];
            var backward = this.isBackward();

            this.eachRange(function(range) {
                func(range);
                ranges.push(range);
            });

            this.removeAllRanges();
            if (backward && ranges.length == 1) {
                this.addRange(ranges[0], "backward");
            } else {
                this.setRanges(ranges);
            }
        };

        selProto.containsNode = function(node, allowPartial) {
            return this.eachRange( function(range) {
                return range.containsNode(node, allowPartial);
            }, true ) || false;
        };

        selProto.getBookmark = function(containerNode) {
            return {
                backward: this.isBackward(),
                rangeBookmarks: this.callMethodOnEachRange("getBookmark", [containerNode])
            };
        };

        selProto.moveToBookmark = function(bookmark) {
            var selRanges = [];
            for (var i = 0, rangeBookmark, range; rangeBookmark = bookmark.rangeBookmarks[i++]; ) {
                range = api.createRange(this.win);
                range.moveToBookmark(rangeBookmark);
                selRanges.push(range);
            }
            if (bookmark.backward) {
                this.setSingleRange(selRanges[0], "backward");
            } else {
                this.setRanges(selRanges);
            }
        };

        selProto.saveRanges = function() {
            return {
                backward: this.isBackward(),
                ranges: this.callMethodOnEachRange("cloneRange")
            };
        };

        selProto.restoreRanges = function(selRanges) {
            this.removeAllRanges();
            for (var i = 0, range; range = selRanges.ranges[i]; ++i) {
                this.addRange(range, (selRanges.backward && i == 0));
            }
        };

        selProto.toHtml = function() {
            var rangeHtmls = [];
            this.eachRange(function(range) {
                rangeHtmls.push( DomRange.toHtml(range) );
            });
            return rangeHtmls.join("");
        };

        if (features.implementsTextRange) {
            selProto.getNativeTextRange = function() {
                var sel, textRange;
                if ( (sel = this.docSelection) ) {
                    var range = sel.createRange();
                    if (isTextRange(range)) {
                        return range;
                    } else {
                        throw module.createError("getNativeTextRange: selection is a control selection");
                    }
                } else if (this.rangeCount > 0) {
                    return api.WrappedTextRange.rangeToTextRange( this.getRangeAt(0) );
                } else {
                    throw module.createError("getNativeTextRange: selection contains no range");
                }
            };
        }

        function inspect(sel) {
            var rangeInspects = [];
            var anchor = new DomPosition(sel.anchorNode, sel.anchorOffset);
            var focus = new DomPosition(sel.focusNode, sel.focusOffset);
            var name = (typeof sel.getName == "function") ? sel.getName() : "Selection";

            if (typeof sel.rangeCount != "undefined") {
                for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                    rangeInspects[i] = DomRange.inspect(sel.getRangeAt(i));
                }
            }
            return "[" + name + "(Ranges: " + rangeInspects.join(", ") +
                    ")(anchor: " + anchor.inspect() + ", focus: " + focus.inspect() + "]";
        }

        selProto.getName = function() {
            return "WrappedSelection";
        };

        selProto.inspect = function() {
            return inspect(this);
        };

        selProto.detach = function() {
            actOnCachedSelection(this.win, "delete");
            deleteProperties(this);
        };

        WrappedSelection.detachAll = function() {
            actOnCachedSelection(null, "deleteAll");
        };

        WrappedSelection.inspect = inspect;
        WrappedSelection.isDirectionBackward = isDirectionBackward;

        api.Selection = WrappedSelection;

        api.selectionPrototype = selProto;

        api.addShimListener(function(win) {
            if (typeof win.getSelection == "undefined") {
                win.getSelection = function() {
                    return getSelection(win);
                };
            }
            win = null;
        });
    });
    

    /*----------------------------------------------------------------------------------------------------------------*/

    // Wait for document to load before initializing
    var docReady = false;

    var loadHandler = function(e) {
        if (!docReady) {
            docReady = true;
            if (!api.initialized && api.config.autoInitialize) {
                init();
            }
        }
    };

    if (isBrowser) {
        // Test whether the document has already been loaded and initialize immediately if so
        if (document.readyState == "complete") {
            loadHandler();
        } else {
            if (isHostMethod(document, "addEventListener")) {
                document.addEventListener("DOMContentLoaded", loadHandler, false);
            }

            // Add a fallback in case the DOMContentLoaded event isn't supported
            addListener(window, "load", loadHandler);
        }
    }

    return api;
}, this);

(function($){
"use strict";

/**
* Gets the value of a property from the style attribute for the first element in the set
* This differs from $.css() which uses getComputedStyle to retrieve the final style applied to the element (which might be inherited)
*
*/
$.fn.style = function(property) {
	var style = '';
	$.each(this, function() {
		var style_attr = $(this).attr('style');
		// if node has a style attribute set
		if(style_attr !== undefined) {
			// extract all properties and look for the specified one
			$.each($.fn.style.explode(';', style_attr.replace(/ /g, '')), function(i, tuple) {
				// extract property name and value
				var style_arr = $.fn.style.explode(':', tuple);
				if(style_arr[0] == property) {
					style = style_arr[1];
					// there should be only one occurence of that property
					// so, once found, we skip the rest
					return false;
				}
			});
		}
		// we process only the first element of the set
		return false;
	});
	return style;
};

/**
* Extracts the whole style attribute of the first element in the set as pairs of properties/values,
* and returns it as an assicative object that can be passed as argument to the method $.css()
*/
$.fn.styles = function() {
	var styles = {};
	$.each(this, function() {
		var style_attr = $(this).attr('style');
		// if node has a style attribute set
		if(style_attr !== undefined) {
			// extract all properties and look for the specified one
			$.each($.fn.style.explode(';', style_attr.replace(/ /g, '')), function(i, tuple) {
				// extract property name and value
				var style_arr = $.fn.style.explode(':', tuple);
				styles[style_arr[0]] = style_arr[1];
			});
		}
		// we process only the first element of the set
		return false;
	});
	return styles;
};


/**
* Returns an array of strings, each of which being a substring formed by splitting the string on boundaries formed by the delimiter.
*/
$.fn.style.explode = function (delimiter, value) {
	var result = [], start = 0, length = value.length;
	while(start < length) {
		var pos = value.indexOf(delimiter, start);
		if(pos == -1) {
			result.push(value.slice(start));
			break;
		}
		result.push(value.slice(start, pos));
		start = pos+delimiter.length;
	}
	return result;
};


/**
*	Returns a set of selected nodes inside the given element
*	The resulting collection might contain elementNodes as well as textNodes (textNodes are returned when some elementNodes are partially selected)
*/
$.fn.selection = function(conf){
	var defaults = {
	};

	return (function ($this, conf) {
		// if no selection is found, result will be an empty array
		var collection = [];
		var sel = rangy.getSelection();
		var range = sel.getRangeAt(0);
		// create a range containing all container ($this) contents
		var container_range = rangy.createRangyRange();
		container_range.selectNodeContents($this[0]);
		// selection is not collapsed and is inside the specified container
		if(!sel.isCollapsed && container_range.containsRange(range)) {
			// split partially selected textNodes so that range only contains fully selected nodes
			range.splitBoundaries();
			// check all selected textNodes
			$.each(range.getNodes([3]), function (i, node) {
				// if the textNode is inside a partially selected elementNode
				if( !range.containsNodeText(node.parentNode) ){
					// add the textNode
					collection.push(node);
				}
				// otherwise, add the parent node (if not already present)
				else if($.inArray(node.parentNode, collection) == -1) {
					collection.push(node.parentNode);
				}
			});
		}
		sel.setSingleRange(range);
		// convert resulting array to a jQuery object
		return $(collection);
	})($(this), $.extend(true, {}, defaults, conf));
};


$.fn.richtext = function(conf) {
	var defaults = {
		toolbar: [
			['Maximize'],['Source'],['Bold','Italic','Underline','Strike','-','Subscript','Superscript', '-', 'RemoveFormat']
		],
		height: '250px'
	};

	var buttons_def = $.fn.richtext.buttons;

	var _init = function ($this, conf) {
		// initialize some internal parameters
		conf.mode = 'wysiwyg';			// wysiwyg | source
		conf.maximized = false;		// true | false
		conf.buttons_states = {};		// { button_name: true | false[, ...] }
	};

	var _layout = function ($this, conf) {
		// create the toolbar
		var $toolbar =
		$('<div/>')
		.addClass('ui-editor-toolbar ui-widget-header ui-corner-all ui-front');

		// create the editor
		var $editor =
		$('<div/>')
		.addClass('ui-editor ui-widget ui-widget-content ui-corner-all')
		.css({'height': conf.height})
		.append($toolbar)
		.append(
			$('<div/>')
			.addClass('ui-editor-content ui-widget-content')
			.attr({contenteditable: true, spellcheck: true})
			.html($this.html())
		)
		.append(
			$('<textarea/>')
			.addClass('ui-editor-source ui-widget-content')
			.attr({spellcheck: false})
			.val($this.html())
			.on('change', function() { $this.trigger('change'); })
			.hide()
		);


		// populate the toolbar (we'll need to know its height just afterward)
		$.each(conf.toolbar, function(i, button_group) {
			if(!$.isArray(button_group)) {
				if(button_group == '/') {
					$toolbar.append( $('<span/>').addClass('ui-editor-toolbar-break') );
				}
				return;
			}
			var $group = $('<span/>').addClass('ui-editor-toolbar-group ui-state-default ui-corner-all');
			$.each(button_group, function(j, button_name) {
				if(button_name == '-') {
					$group.append( $('<span/>').addClass('ui-editor-toolbar-separator') );
				}
				// if we have a definition for that button and if it is not yet in the toolbar
				else if(typeof buttons_def[button_name] != 'undefined' && typeof conf.buttons_states[button_name] == 'undefined') {
					// init/set the button state
					conf.buttons_states[button_name] = false;
					var $button =
					$('<span/>')
					.attr('title', button_name)
					.attr('button', button_name)
					.button({icons:{primary:buttons_def[button_name].icon}, text: false})
					.appendTo($group);
				}
			});
			$group.appendTo($toolbar);
		});

		// adjust height of content and source
		var $temp = $('<div/>').css({'position': 'absolute', 'top': '0', 'left': '-9999px'}).append($editor).appendTo('body');		
		$('.ui-editor-content,.ui-editor-source', $editor).css('height', (parseInt($editor.css('height'))-parseInt($toolbar.css('height'))-6)+'px');
		$editor.detach();

		// provide a quick access to the editor
		$this.data('editor', $editor);

		// insert editor element regarding the given element
		if($this.parent().length === 0) {
			if($this.prop('nodeName').toUpperCase() == 'TEXTAREA') {
				console.log('qRichtext error : cannot instanciate on detached TEXTAREA, insert or wrap it first or use DIV instead');
			}
			else {
				$this.empty().append($editor);
			}
		}
		else {
			$editor.insertBefore( $this.hide() );
			if($this.attr('name') !== undefined) {
				$('.ui-editor-source', $editor).attr('name', $this.attr('name'));
				$this.removeAttr('name');
			}
		}
	};

	var _listen = function($this, conf) {
		var $editor = $this.data('editor');

		$('.ui-button', $editor)
		.on('click', function () {
			var button_name = $(this).attr('title');
			switch (buttons_def[button_name].type) {
			case 'action':
				return buttons_def[button_name].click($editor, conf);
				break;
			case 'style':
				switch (buttons_def[button_name].method) {
				case 'css':
					var style = {};
					var $selection = $({});
					style[buttons_def[button_name].style.property] = buttons_def[button_name].style.value;
					if(!conf.buttons_states[button_name]) {
						$selection = methods.addCSS($editor.selection(), style);
						methods.setButtonState($this, conf, button_name, true);
					}
					else {
						$selection = methods.removeCSS($editor.selection(), style);
						methods.setButtonState($this, conf, button_name, false);
					}
					$.fn.richtext.updateSelection($selection);
					break;
				case 'wrap':
					if(!conf.buttons_states[button_name]) {
						methods.wrap($editor.selection(), buttons_def[button_name].tag);
						methods.setButtonState($this, conf, button_name, true);
					}
					else {
						methods.unwrap($editor.selection(), buttons_def[button_name].tag);
						methods.setButtonState($this, conf, button_name, false);
					}
					break;
				case 'wrapAll':
					if(!conf.buttons_states[button_name]) {
						var style = {};
						if(typeof buttons_def[button_name].style != 'undefined') {
							style[buttons_def[button_name].style.property] = buttons_def[button_name].style.value;
						}
						methods.wrapAll($editor.selection(), buttons_def[button_name].tag, style);
						methods.setButtonState($this, conf, button_name, true);
// todo : necessary because justify buttons are exclusive : how to generalize ?
						methods.updateButtonsStates($this, conf);
					}
					else {
						methods.unwrapAll($editor.selection(), buttons_def[button_name].tag);
						methods.setButtonState($this, conf, button_name, false);
					}
					break;
				}
				break;
			}
			methods.updateValue($this, conf);
		});

		$('.ui-editor-content', $editor)
		.on('focus', function(event){
		})
		.on('select', function(event){
			// note: select event don't seem to be triggered on contenteditable div
		})
		.on('click', function(event){
		})
		.on('dblclick', function(event){
			// when we end up here, click event has already been triggered twice
		})
		.on('mousedown', function(event) {
		})
		.on('mouseup', function(event) {
			// caret position or selection might have changed : update buttons states
			methods.updateButtonsStates($this, conf);
		})
		.on('keyup', function(event) {
			if(!event.shiftKey) {
				// caret position or selection might have changed : update buttons states
				methods.updateButtonsStates($this, conf);
			}
		})
		.on('keydown', function(event) {
			// handle carriage return output
			if (event.which == 13) {
				var range = rangy.getSelection().getRangeAt(0);
				var parentNode = range.startContainer.parentNode;
				// we are inside a list item : insert a new item after the current one
				if(parentNode.nodeName.toUpperCase() == 'LI') {
					range.splitBoundaries();
					var start = range.startContainer;
					// if we are in the middle of an element, select all parts after the caret
					if(start.nextSibling) {
						range.selectNodeContents(parentNode);
						range.setStart(start.nextSibling);
						range.select();
					}
					//  create new item and add the current selection to it
					var $li = $('<li/>')
					.append($editor.selection().detach());
					$(start).parent().after($li);
					range.selectNode($li[0]);
					// set the caret position at the beginning of the new item
					range.collapse(true);
					range.select();
				}
				// in all other cases, just add a line break
				else {
					var enter = $('<br/>')[0];
					range.collapse(true);
					range.insertNode(enter);
					range.setStartAfter(enter);
					range.select();
				}
				// prevent the default behaviour
				return false;
			}
			methods.updateValue($this, conf);
		});
	};


	var methods = {

		addCSS: function($selection, style) {
			return $.each($.fn.richtext.normalize($selection), function(i, node) {
				var $node = $(this);
				$.each(style, function (property, value) {
					// check the current value for the targeted property
					// note : we don't use $.css(property) which calls getComputedStyle and might return inherited style and mixed types values (ex: 700 as well as 'bold')
					var current_value = $node.style(property);
					// if style is not applied on element
					if(current_value.indexOf(value) == -1) {
						// if current value is not empty we add the specified value to it
						if($.inArray(current_value, [null, '', '0', 'none']) == -1) {
							value = current_value+' '+value;
						}
						// apply the specified style (if current value is empty, we overwrite it)
						$node.css(property, value);
					}
				});
			});
		},

		removeCSS: function($selection, style) {
			$.each($.fn.richtext.normalize($selection), function(i, node) {
				var $node  = $(this);
				$.each(style, function (property, value) {
					var node_style =  $node.attr('style');
					var current_property = $node.style(property);
					// if property is inherited
					if(node_style === undefined || node_style.indexOf(value) == -1) {
						// find the node from which property is inherited
						var $parent = $node.parent();
						do {
							current_property = $parent.style(property);
							// extract node from its parent (there might be several levels here)

							// example : remove bold style from ghi
							// <span style="font-weight: bold;">def<span style="font-style: italic;"><span>ghi</span>abc</span></span>

							// 1) <span style="font-weight: bold;">def<span style="font-style: italic;"></span><span>ghi</span><span style="font-style: italic;">abc</span></span>
							// 2) <span style="font-weight: bold;">def<span style="font-style: italic;"></span></span><span>ghi</span><span style="font-weight: bold;"><span style="font-style: italic;">abc</span></span>
							// 3) <span style="font-weight: bold;">def</span><span style="font-style: italic;">ghi</span><span style="font-weight: bold;"><span style="font-style: italic;">abc</span></span>

							// add some marker
							$node.addClass('qRtMoving');
							var tag = $parent.prop('nodeName');
							var $next_parent = $parent.parent();

							var $new =
								$('<'+tag+'>'+
								$parent.html().replace(new RegExp($node[0].outerHTML), '</'+tag+'>'+$node[0].outerHTML+'<'+tag+'>')+
								'</'+tag+'>')	;

							$parent.replaceWith($new);

							// retrieve node, remove marker and restore style
							$node  = $('.qRtMoving', $next_parent).removeAttr('class').attr('style', node_style);
							// update selection
							$selection[i] = $node[0];
							// add style from previous parent to node and its direct siblings
							$node
							.add($node.prev())
							.add($node.next())
							.css($parent.styles());
							// normalize the new parent  (we might have generated some empty tags)
							$.fn.richtext.normalize($next_parent.children(), false);
							// try the above parent
							$parent = $next_parent;
						} while(current_property.indexOf(value) == -1) ;
					}
					// remove property
					$node.css(property, $node.style(property).replace(new RegExp(value, 'g'), ''));
				});
			});
			return $selection;
		},

		wrap: function($selection, tag) {
			// wrap nodes not already wrapped by such tag
			$.each($selection, function () {
				if($(this).closest(tag).not('.ui-editor-content').length === 0) {
					$(this).wrap($('<'+tag+'/>'));
				}
			});
		},

		unwrap: function($selection, tag) {
			$.each($selection, function() {
				var $node = $(this);
				// node is a textNode
				if(this.nodeType == 3) {
					// node is either first node or last node of its parent
					if(this.parentNode.firstChild == this) {
						$node.parent().before($node.detach());
					}
					else {
						$node.parent().after($node.detach());
					}
					// continue iteration
					return;
				}
				// node is the wrapper itself
				if($node.prop('nodeName').toUpperCase() == tag.toUpperCase()) {
					$node.before($node.html()).remove();
				}
				// node is an element wrapped into the tag
				else {
					$node.unwrap();
				}
			});
		},

		wrapAll: function($selection, tag, style) {
			if($selection.prop('nodeName') && $selection.prop('nodeName').toUpperCase() == tag.toUpperCase()) {
				$selection.css(style);
			}
			else {
				var $parent = $.fn.richtext.normalize($selection).first().parent();
				if( $parent.prop('nodeName').toUpperCase() == tag.toUpperCase() && !$parent.hasClass('ui-editor-content') ) {
					$parent.css(style);
				}
				else {
					// note: $selection does not reflect the accurate innerHTML, so we cannot simply apply $.wrapAll() on it
					$parent.append( $('<'+tag+'/>').css(style).append($parent.children().detach()) );
				}
			}

		},

		unwrapAll: function($selection, tag) {
			// node is the wrapper itself
			if($selection.prop('nodeName') && $selection.prop('nodeName').toUpperCase() == tag.toUpperCase()) {
				$selection.before($selection.html()).remove();
			}
			else {
				$selection.unwrap();
			}
		},

		/**
		* synchronizes editor-source (textarea with attribute 'name' for form submission) and editor-content (contenteditable)
		* note: this value is the one returned by method .richtext('value')
		*/
		updateValue: function($this, conf) {
			if(conf.timeoutId) {
				clearTimeout(conf.timeoutId);
			}
			conf.timeoutId = setTimeout(function(){
				conf.timeoutId = null;
				var $editor = $this.data('editor');
				$('.ui-editor-source', $editor).val( $('.ui-editor-content', $editor).html() );
				$this.trigger('change');
			}, 500);
		},

		setButtonState: function ($this, conf, button_name, state) {
			var $button = $('span[button="'+button_name+'"]', $this.data('editor'));
			if(state) {
				conf.buttons_states[button_name] = true;
				$button.addClass('ui-state-highlight');
			}
			else {
				conf.buttons_states[button_name] = false;
				$button.removeClass('ui-state-highlight');
			}
		},

		updateButtonsStates: function($this, conf) {
			var $editor = $this.data('editor');
			var sel = rangy.getSelection();

			var $selection;

			// if there is no selected node, use the selection anchor node as selection
			if(sel.isCollapsed) {
				$selection = $(sel.anchorNode);
			}
			else {
				$selection = $editor.selection();
			}
			if($selection === undefined || $selection.length === 0) {
				return;
			}
			// 1) find the resulting style of the first element in the selection
			var $node = $selection.first();
			var getAppliedStyles = function($node) {
				var styles = {};
				$.each(buttons_def, function(button_name, button_def) {
					var $button = $('span[button="'+button_name+'"]', $editor);
					// if toolbar contains such button
					if($button.length) {
						// we're only interested in buttons related to content styling
						if(button_def.type != 'style') {
							// continue iteration
							return;
						}
						styles[button_name] = false;
						if(button_def.method == 'css') {
							// $.css() uses method getComputedStyle() wich applies only to elementNodes
							if($node[0].nodeType == 3) {
								$node = $( $node[0].parentNode );
							}
							if($node.css(button_def.style.property).indexOf(button_def.style.value) > -1) {
								styles[button_name] = true;
							}
						}
						else if(button_def.method == 'wrap'){
							if( $node.prop('nodeName').toUpperCase() == button_def.tag.toUpperCase()
								 || $node.parents(button_def.tag).length > 0 ) {
								styles[button_name] = true;
							}
						}
						else if(button_def.method == 'wrapAll'){
							if(typeof button_def.style == 'undefined') {
								if($node.parentsUntil($('.ui-editor-content', $editor), button_def.tag).length > 0) {
									styles[button_name] = true;
								}
							}
							else if($node.css(button_def.style.property) == button_def.style.value) {
								styles[button_name] = true;
							}
						}
					}
				});
				return styles;
			};

			var applied_styles = getAppliedStyles($node);

			// 2) reduce the applied styles by comparing with the other nodes in the selection
			$.each($selection.slice(1), function (){
				var styles = getAppliedStyles($(this));
				var is_empty = true;
				$.each(applied_styles, function (button_name, state) {
					applied_styles[button_name] = (applied_styles[button_name] && styles[button_name]);
					if(applied_styles[button_name]) {
						is_empty = false;
					}
				});
				// if resulting style is empty, stop iteration
				if(is_empty) return false;
			});

			$.each(applied_styles, function(button_name, state) {
				methods.setButtonState($this, conf, button_name, state);
			});
		}

	};

	if(typeof conf == 'object') {
		return $.each(this, function() {
			return (function ($this, conf) {
				_init($this, conf);
				_layout($this, conf);
				_listen($this, conf);
				return $this;
			})($(this), $.extend({}, defaults, conf));
		});
	}
	else if(typeof conf == 'string') {
		switch(conf){
		case 'value':
			return $('.ui-editor-source', $(this).data('editor')).val();
			break;
		}
	}
	return this;
};


/**
* This method allows to make sure all items among a selection are consistents
*
* @param $selection jQuery A jQuery collection of nodes
* @param wrap boolean  If set to true, the methode wraps all textNodes from selection inside SPAN nodes. If set to false, it removes empty SPAN nodes and extract textNodes from SPAN nodes having no style attribute set.
* @return jQuery object
*/
$.fn.richtext.normalize = function ($selection, wrap) {
	var args = arguments;
	// default action is to wrap textNodes
	if(args.length == 1) {
		args[1] = true;
	}
	return $.each($selection, function(i, node) {
		var $node = $(this);
		// A) wrap textNodes into span tags
		if(args[1]) {
			if(this.nodeType == 3) {
				$selection[i] = $node.wrap($('<span/>')).parent()[0];
			}
		}
		// B) remove unnecessary span wrappers
		else {
			if(this.nodeType == 1 && $node.prop('nodeName').toUpperCase() == 'SPAN') {
				var style_attr = $node.attr('style');
				// if node has no style left, convert it to textNode
				if($node[0].innerHTML.length === 0 || style_attr === undefined || style_attr.length === 0) {
					// $.children() doesn't seem to handle textNodes properly
					var $children = $( $node[0].childNodes );
					$node.before($children.detach()).remove();
					// update selection
					$selection[i] = $children[0];
				}
			}
		}
	});
};

$.fn.richtext.updateSelection = function($selection) {
	var range = rangy.getSelection().getRangeAt(0);
	if($selection.length) {
		range.setStartBefore($selection.first()[0]);
		range.setEndAfter($selection.last()[0]);
		range.select();
	}
};


/**
* $.fn.richtext.buttons property holds the definitions of the available buttons for the richtext plugin
* At first, we define some basic buttons,  more are defined below by extending this property
*/
$.fn.richtext.buttons = {
	'Maximize': {
		icon: 'ui-icon-editor-maximize',
		type: 'action',
		click: function ($editor, conf) {
			var $toolbar = $('.ui-editor-toolbar', $editor);
			if(conf.maximized) {
				conf.maximized = false;
				// restore size
				$editor.css({'position': 'relative', 'width': conf.width, 'height': conf.height});
				// restore parent
				conf.parent.append($editor.detach());
				// restore body children
				$('body').append(conf.body_children);
			}
			else {
				conf.maximized = true;
				// save the minimized width
				conf.width = $editor.css('width');
				// save original parent
				conf.parent = $editor.parent();
				// save original body content
				conf.body_children = $('body').children().detach();
				$editor
				.detach()
				.appendTo('body')
				.css({'position': 'absolute', 'width': '100%', 'height': '100%', 'z-index': '9999', 'top': '0px', 'left': '0px'});
			}
			$('.ui-editor-content,.ui-editor-source', $editor).css('height', (parseInt($editor.css('height'))-parseInt($toolbar.css('height'))-6)+'px');
		}
	},
	'Source': {
		icon: 'ui-icon-editor-source',
		type: 'action',
		click: function ($editor, conf) {
			var $content = $('.ui-editor-content', $editor);
			var $source = $('.ui-editor-source', $editor);
			if(conf.mode == 'wysiwyg') {
				conf.mode = 'source';
				$source.val($content.html());
				// disable all buttons except this one
				$('.ui-button', $editor).not($('.ui-button[title="Source"]', $editor)).button({ disabled: true });
			}
			else if(conf.mode == 'source') {
				conf.mode = 'wysiwyg';
				$content.html($source.val());
				$('.ui-button', $editor).button({ disabled: false });
			}
			$content.toggle();
			$source.toggle();
		}
	},
	'Italic': {
		icon: 'ui-icon-editor-italic',
		type: 'style',
		method: 'css',
		style: {
			property: 'font-style',
			value: 'italic'
		}
	},
	'Underline': {
		icon: 'ui-icon-editor-underline',
		type: 'style',
		method: 'css',
		style: {
			property: 'text-decoration',
			value: 'underline'
		}
	},
	'Strike': {
		icon: 'ui-icon-editor-strike',
		type: 'style',
		method: 'css',
		style: {
			property: 'text-decoration',
			value: 'line-through'
		}
	},
	'Bold': {
		icon: 'ui-icon-editor-bold',
		type: 'style',
		method: 'css',
		style: {
			property: 'font-weight',
			value: 'bold'
		}
	},
	'Subscript': {
		icon: 'ui-icon-editor-subscript',
		type: 'style',
		method: 'wrap',
		tag: 'sub'
	},
	'Superscript' : {
		icon: 'ui-icon-editor-superscript',
		type: 'style',
		method: 'wrap',
		tag: 'sup'
	},
	'RemoveFormat': {
		icon: 'ui-icon-editor-removeformat',
		type: 'action',
		click: function($editor, conf) {
			$.fn.richtext.updateSelection($.fn.richtext.normalize($editor.selection().removeAttr('style'), false));
		}
	},
	'Blockquote': {
		icon: 'ui-icon-editor-blockquote',
		type: 'style',
		method: 'wrapAll',
		tag: 'blockquote'
	},
	'Anchor': {
		icon: 'ui-icon-editor-anchor',
		type: 'action',
		click: function($editor, conf) {
		}
	},
	'Link': {
		icon: 'ui-icon-editor-link',
		type: 'action',
		click: function($editor, conf) {
		}
	},
	'Image': {
		icon: 'ui-icon-editor-image',
		type: 'action',
		click: function($editor, conf) {
		}
	},
	'Table': {
		icon: 'ui-icon-editor-table',
		type: 'action',
		click: function($editor, conf) {
		}
	}
};

/**
*	Main actions
*/
$.extend(true, $.fn.richtext.buttons, {
	'Undo': {
		icon: 'ui-icon-editor-undo',
		type: 'action',
		click: function ($editor, conf) {
			document.execCommand('undo', false, null);
		}
	},
	'Redo': {
		icon: 'ui-icon-editor-redo',
		type: 'action',
		click: function ($editor, conf) {
			document.execCommand('redo', false, null);
		}
	},
	'Cut': {
		icon: 'ui-icon-editor-cut',
		type: 'action',
		click: function ($editor, conf) {
			var range = rangy.getSelection().getRangeAt(0);
			conf.clipboard = range.extractContents();
		}
	},
	'Copy': {
		icon: 'ui-icon-editor-copy',
		type: 'action',
		click: function ($editor, conf) {
			var range = rangy.getSelection().getRangeAt(0);
			conf.clipboard = range.cloneContents();
		}
	},
	'Paste': {
		icon: 'ui-icon-editor-paste',
		type: 'action',
		click: function ($editor, conf) {
			var range = rangy.getSelection().getRangeAt(0);
			range.splitBoundaries();
			$(range.startContainer).after($(conf.clipboard.childNodes));
		}
	}
});

/**
*	Text justify
*/
$.extend(true, $.fn.richtext.buttons, {
	'JustifyLeft': {
		icon: 'ui-icon-editor-justifyleft',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'left'
		}
	},
	'JustifyCenter': {
		icon: 'ui-icon-editor-justifycenter',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'center'
		}
	},
	'JustifyRight': {
		icon: 'ui-icon-editor-justifyright',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'right'
		}
	},
	'JustifyBlock': {
		icon: 'ui-icon-editor-justifyblock',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'justify'
		}
	}
});

/*
*	Lists and indentation
*/
$.extend(true, $.fn.richtext.buttons, {
	'NumberedList': {
		icon: 'ui-icon-editor-numberedlist',
		type: 'action',
		click: function($editor, conf) {
			var $selection = $editor.selection();
			if($selection.length === 0) {
				var range = rangy.getSelection().getRangeAt(0);
				range.splitBoundaries();
				$(range.startContainer).after($('<ol><li/></ol>'));
			}
			else {
				$.fn.richtext.normalize($selection).wrapAll('<ol><li/></ol>');
			}
		}
	},
	'BulletedList': {
		icon: 'ui-icon-editor-bulletedlist',
		type: 'action',
		click: function($editor, conf){
			var $selection = $editor.selection();
			if($selection.length === 0) {
				var range = rangy.getSelection().getRangeAt(0);
				range.splitBoundaries();
				$(range.startContainer).after($('<ol><li/></ol>'));
			}
			else {
				$.fn.richtext.normalize($selection).wrapAll('<ul><li/></ul>');
			}
		}
	},
	'Outdent': {
		icon: 'ui-icon-editor-outdent',
		type: 'action',
		click: function($editor, conf){

		}
	},
	'Indent': {
		icon: 'ui-icon-editor-indent',
		type: 'action',
		click: function($editor, conf) {
		}
	}
});

/**
*	Text color
*	This button requires evol.colorpicker plugin
*/
$.extend(true, $.fn.richtext.buttons, {
	'TextColor': {
		icon: 'ui-icon-editor-textcolor',
		type: 'action',
		click: function($editor, conf) {
			var $picker = $('#qRtColorPicker', $editor);
			// at first call, instanciate the color picker
			if($picker.length === 0) {
				$picker = $('<div/>')
					.attr('id', 'qRtColorPicker')
					.css({'position': 'absolute'})
					.colorpicker({color:'#31859b'})
					.on('change.color', function(event, color){
							$.fn.richtext.normalize($editor.selection()).css('color', color);
							$picker.hide();
					})
					.appendTo($('.ui-editor-toolbar', $editor));
					// hide color picker if user clicks somewhere else on the editor
					$editor.on('click.colorpicker', function () {
						$picker.hide();
					});
			}
			else {
				// color picker already exists, clicking the button toggles its visibility
				$picker.toggle();
			}
			return false;
		}
	}
});

})(jQuery);
(function($, qinoa){

/* This object holds the methods for rendering qSearch widgets
*  and can be extended to handle additional widgets
*
*/
qinoa.SearchWidgets = {
	'string': function ($this, conf) {
		var $widget = $('<input type="text"/>')
		.attr({id: conf.id, name: conf.name})
		// set layout and use jquery-UI css
		.addClass('ui-widget')
		.css('margin-right', '10px')
		// assign the specified value
		.val(conf.value);
		return $widget;
	},
	'short_text':  function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},	
	'text':  function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},	
	'binary':  function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'boolean': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'integer': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'float': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'many2one': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'time': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'timestamp': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},	
	'date': function ($this, conf) {
		var $widget = $('<input type="text"/>')
		.attr({id: conf.id, name: conf.name})
		.css('margin-right', '10px')
		.daterangepicker({
			dateFormat: 'yy-mm-dd',
			presetRanges: [
			{	
				text: 'Today', 
				dateStart: 'today', 
				dateEnd: 'today' 
			},
			{
				text: 'The previous Month', 
				dateStart: function(){ return Date.parse('1 month ago').moveToFirstDayOfMonth();  }, 
				dateEnd: function(){ return Date.parse('1 month ago').moveToLastDayOfMonth();  } 
			},
			{
				text: 'The previous Year', 
				dateStart: function(){ return Date.parse('12 months ago').moveToLastDayOfMonth();  }, 
				dateEnd: function(){ return Date.parse('1 day ago');  } 
			}
			],
			presets: {
				specificDate: 'Specific Date',
				dateRange: 'Date Range'
			},
			earliestDate: Date.parse('-70years'),
			latestDate: Date.parse('+20years'),
			datepickerOptions: {
				changeMonth: true, 
				changeYear: true, 
				yearRange: 'c-70:c+20'
			}
		})
		return $widget;
	},
	'datetime': function ($this, conf) {
		return qinoa.SearchWidgets.date($this, conf);
	}
};

$.fn.qSearchWidget = function(conf){

	var default_conf = {
		id: null,
		name: null,
		value: null,
		type: 'string'
	};
	
	return this.each(function() {
		return (function ($this, conf) {
			try {
				if(typeof qinoa.SearchWidgets[conf.type] == 'undefined') throw Error('Error raised in qinoa-ui.qSearchWidget : unknown type '+conf.type);
				var $widget = qinoa.SearchWidgets[conf.type]($this, conf);
				return $this.data('widget', $widget.appendTo($this));
			}
			catch(e) {
				qinoa.console.log(conf.type+' '+conf.name+e.message);
			}
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery, qinoa);
/*! 
* qinoa-ui.qSearch - v1.0.0
* https://github.com/cedricfrancoys/qinoa
* Copyright (c) 2015 Cedric Francoys; Licensed GPLv3 */

/**
 * qinoa-ui.qSearch : A plugin generating a list of objects with search options (this plugin is a wrapper for qGrid)
 *
 * Author	: Cedric Francoys
 * Launch	: March 2015
 * Version	: 1.0
 *
 * Licensed under GPL Version 3 license
 * http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
(function($){

$.fn.qSearchGrid = function(conf){
	var default_conf = {
	// mandatory params
		class_name:		'',									// class of the objects to list
		view:			'list.default',						// name of the view to use
        ui:             qinoa.conf.user_lang
	};

	var methods = {
		layout: function($this, conf) {
			var deferred = $.Deferred();

			var schema, fields, lang;
			$({})
			.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}) })            
            .queue(	function (next) { $.when(qinoa.get_fields(conf.class_name, conf.view)).done(function (result) {fields = result; next(); }) })
			.queue(	function (next) { $.when(qinoa.get_lang(conf.class_name, conf.ui)).done(function (result) {lang = result; next(); }) })
			.queue( function (next) {
				// create inputs for critereas (simple fields only)
				// (we make it very basic for now)
				var $search_criterea = $('<div/>').css('width', '100%');

				$.each(fields, function(field, attributes){
					// copy var attributes to a configuration object
					var config = $.extend({}, attributes);
					if(	($.inArray(schema[field]['type'], qinoa.simple_types) >= 0)
						||
						(schema[field]['type'] == 'function' >= 0 && $.inArray(schema[field]['result_type'], qinoa.simple_types) >= 0 && schema[field]['store'] == true)) {

						var type = (schema[field]['type'] == 'function')?schema[field]['result_type']:schema[field]['type'];
// todo : move to translate method
						var field_label = field;
						if(!$.isEmptyObject(lang) && typeof lang['model'][field] != 'undefined' && typeof lang['model'][field]['label'] != 'undefined') {
							field_label = lang['model'][field]['label'];
						}

						$widget = $('<span/>').qSearchWidget({
						id:		field+(new Date()).getTime(),
						name:	field,
						type:	type
						});

						$search_criterea.append(
							$('<div/>')
							.css({'float': 'left', 'margin-bottom': '2px'})
							.append(
								$('<div/>')
								.append(
									$('<label/>')
									.attr('for', field)
									.css({
										'float': 'left',
										'text-align': 'right',
										'width': '80px',
										'margin-right': '4px'
									})
									.text(field_label)
								)
								.append($widget)
							)
						);

					}
				});
				// create the grid
				var $grid = $('<div/>')
				.qGrid(conf)
				.on('ready', function() {
					// remember the original domain
					var grid_domain_orig = $.extend(true, [], $grid.data('conf').domain);
					// create the search button and the associated action when clicking
					var $search = $('<div/>')
					.append($('<table/>')
							.append($('<tr/>')
									.append($('<td>')
											.attr('width', '90%')
											.append($search_criterea))
									.append($('<td>')
											.append($('<button type="button"/>')
													.css('margin-bottom', '2px')
													.text('search')
													.button()
													.on('click', function(){
														// 1) generate the new domain (array of conditions)
														var grid_conf = $grid.data('conf');
														// reset the domain to its original state
														grid_conf.domain = $.extend(true, [], grid_domain_orig);
														$('input', $search).each(function(){
															var $item = $(this);
															var field = $item.attr('name');
															var value = $item.val();
															if(value.length) {
																// reset the number ofmatching records
																grid_conf.records = '';
																// create the new domain to filter the results of the grid
																type = schema[field]['type'];
																if(schema[field]['type'] == 'function') type = schema[field]['result_type'];
																switch(type) {
																	case 'boolean':
																	case 'integer':
																	case 'many2one':
																	case 'selection':
																	case 'time':
																	case 'timestamp':
																		grid_conf.domain[0].push([ field, '=', value]);
																		break;
																	case 'datetime':
																	case 'date':
																		// may be a date range (separator: '-')
																		var date_array = value.split(" - ");
																		switch(date_array.length) {
																			case 1:
																				// only one date
																				grid_conf.domain[0].push([ field, '=', value ]);
																				break;
																			case 2:
																				// date range
																				grid_conf.domain[0].push([ field, '>=', date_array[0] ]);
																				grid_conf.domain[0].push([ field, '<=', date_array[1] ]);
																				break;
																		}
																		break;
																	case 'string':
																	case 'short_text':
																	case 'text':
																	case 'binary':
																		// note: remember that binary type may hold field translation
																		grid_conf.domain[0].push([ field, 'ilike', '%' + value + '%']);
																		break;
																}
															}
															});

															// 2) force grid to refresh
															$grid.trigger('reload');
														})
												)
										)
								)
						);
					$this.append($search).append($grid);
					// if conf is requested, return grid conf
					$this.data('conf', $grid.data('conf'));
					deferred.resolve();
				});

			});
			return deferred.promise();
		},

		translate: function($this, conf) {
		}
	};

	return this.each(function() {
		return (function ($this, conf) {
			$.when(methods.layout($this, conf))
			.done(function () {
				$this.trigger('ready');
			});

			return $this;
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery);
// require jquery-1.7.1.js (or later), ckeditor.js, jquery-ui.timepicker.js, easyObject.grid.js, easyObject.dropdownlist.js, easyObject.choice.js
// jquery.inputmask.js

// accepted types are: boolean, float, integer, string, short_text, text, date, time, datetime, timestamp, selection, binary, one2many, many2one, many2many
// and soe additional types : password, image


// @deprecated

(function($){

	$.fn.editable = function(conf){
		var default_conf = {
			name: '',
			value: '',
			mode: 'edit',
			type: 'string',			
			format: '',
			align: 'left',		
			readonly: false,
			required: false,
			onchange: function() {}

		}

		return this.each(function() {
			return (function ($this, conf) {
                $this.attr('mode', conf.mode);
                $this.data('value', conf.value);
				switch(conf.type) {
						case 'boolean':
                            $this
							.on('render', function() {
                                $this.empty();                             
                                $('<input type="checkbox" />')
                                .attr({id: conf.name, name: conf.name})
                                .prop('checked', (parseInt(conf.value) > 0))                               
                                .on('change', function() {
                                    $this.data('value', +(this.checked));
                                    $this.trigger('change');
                                })
                                .val((parseInt($this.data('value')) > 0)?1:0)
                                .appendTo($this);
                            });                                        
							break;
						case 'integer':
							var $widget = $('<input type="text"/>')
												.attr({id: conf.name, name: conf.name})
												.css({'width': '100%', 'text-align': conf.align})
												.val(conf.value)
												.on('change', conf.onchange);
						
							$widget.inputmask("integer",  { allowMinus: true });
							if(conf.readonly) $widget.attr("disabled","disabled");
							
							break;
						case 'float':
							var $widget = $('<input type="text"/>')
												.attr({id: conf.name, name: conf.name})
												.css({'width': '100%', 'text-align': conf.align})
												.val(conf.value)
												.on('change', conf.onchange);
						
							$widget.inputmask("decimal", { radixPoint: "." , digits: 2, autoGroup: false});
							if(conf.readonly) $widget.attr("disabled","disabled");
							
							break;
						case 'string':
							$this
							.on('toggle', function() {
								$this.attr('mode', ($this.attr('mode') == 'view')?'edit':'view');
								$this.trigger('render');
							})
							.on('render', function() {
								$this.empty();
								if($this.attr('mode') == 'edit') {
									$('<input type="text"/>')
									.attr({id: conf.name, name: conf.name})
									.css({'width': '100%', 'text-align': conf.align})									
									.on('change', function() {
										$this.data('value', $(this).val());
										$this.trigger('change');
									})
									.val($this.data('value'))
									.appendTo($this);													
								}
								else {
									$('<div/>')
									.css({'width': '100%', 'text-align': conf.align})									
									.html($this.data('value'))
									.appendTo($this);									
								}						
							});
							break;
						case 'selection':                          
                            $this
							.on('render', function() {
                                $this.empty();
                                var $options = $('<div />');
                                $.each(conf.selection, function(value, display) {
                                    $option = $('<option />').attr('value', value).text(display);
                                    if(value == conf.value) $option.attr('selected', 'selected');
                                    $options.append($option);
                                });                                
                                $('<select />')
                                .attr({id: conf.name, name: conf.name})
                                .css({'width': '100%', 'text-align': conf.align})
                                .append($options.children())                                
                                .on('change', function() {
                                    $this.data('value', $(this).val());
                                    $this.trigger('change');
                                })
                                .val($this.data('value'))
                                .appendTo($this);
                            });
							break;                            
				}
				return $this.trigger('render');
			})($(this), $.extend(true, default_conf, conf));
		});
	};
})(jQuery);

//Qinoa.utils.js

// JS equivalents to convenient PHP functions
function rtrim(value) {
	while(value.charAt(value.length-1) == ' ') value = value.slice(0,-1);
	return value;
}

function ucfirst(value) {
	if(typeof(value) == 'string') return value.charAt(0).toUpperCase() + value.substr(1);
	return '';
}

function lcfirst(value) {
	if(typeof(value) == 'string') return value.charAt(0).toLowerCase() + value.substr(1);
	return '';
}

function explode(delimiter, value) {
	var result = [];
	var start = 0, length = value.length;
	while(start < length) {
		pos = value.indexOf(delimiter, start);
		if(pos == -1) {
			result.push(value.slice(start));
			break;
		}
		result.push(value.slice(start, pos));
		start = pos+delimiter.length;
	}
	return result;
}

// other utility functions
function remove_value(list, value) {
	var result = [];
	for(i in list) if(list[i] != value) result.push(list[i]);
	return result;	
}

function add_value(list, value) {
	var result = remove_value(list, value);
	result.push(value);
	return result;
}

function merge_domains(domA, domB) {
	result = [];
	for (x in domA) {
		for (y in domB) {	
			x.push(y);
			result.push([x]);		
		}
	}
	return result;
}
