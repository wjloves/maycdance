var MonHead = [],
    yearElement,
    mouthElement,
    dayElement;

function YYYYMMDDstart(){
    MonHead = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    yearElement = $('select[name="year"]');
    mouthElement = $('select[name="mouth"]');
    dayElement = $('select[name="day"]');
    //先给年下拉框赋内容
    var y  = new Date().getFullYear(),
        mouthOPtions = "",
        yearOptions = "";
    for (var i = (y-30); i < (y+30); i++) {//以今年为准，前30年，后30年
        yearOptions += "<option value='"+i+"'>"+i+"</option>";
    }
    yearElement.append(yearOptions);

    //赋月份的下拉框
    for (var i = 1; i < 13; i++) {
        mouthOPtions += "<option value='"+i+"'>"+i+"</option>";
    }
    mouthElement.append(mouthOPtions);
}

if(document.attachEvent)
    window.attachEvent("onload", YYYYMMDDstart);
else
    window.addEventListener('load', YYYYMMDDstart, false);

function YYYYDD(str) //年发生变化时日期发生变化(主要是判断闰平年)
{
    var MMvalue = mouthElement.val();
    if (MMvalue == ""){
        optionsClear(dayElement);
        return;
    }
    var n = MonHead[MMvalue - 1];
    if (MMvalue ==2 && IsPinYear(str)) n++;
    writeDay(n)
}
function MMDD(str)   //月发生变化时日期联动
{
    var YYYYvalue = yearElement.val();
    if (YYYYvalue == ""){
        optionsClear(dayElement); return;
    }
    var n = MonHead[str - 1];
    if (str ==2 && IsPinYear(YYYYvalue)) n++;
    writeDay(n)
}
function writeDay(n)   //据条件写日期的下拉框
{
    var dayOptions = "";
    var selectDay = dayElement.val();
    var selectMouth = mouthElement.val();
    optionsClear(dayElement);
    for (var i=1; i<(n+1); i++) {
        dayOptions += "<option value='"+i+"'>"+i+"</option>";
    }
    dayElement.append(dayOptions);
    if( (selectMouth == 2 && selectDay > 29 && IsPinYear) || (selectMouth == 2 && selectDay > 28)) {
        dayElement.val("");
    }
    else {
        dayElement.val(selectDay);
    }
}

function IsPinYear(year)//判断是否闰平年
{
    return(0 == year%4 && (year%100 !=0 || year%400 == 0));
}
function optionsClear(e)
{
    e.html("<option value=''>日</option>");
}