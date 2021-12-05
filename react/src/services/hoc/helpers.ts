let lastId = 0;
export const uid = (prefix: string='id') => {
    lastId++;
    return `${prefix}-${lastId}`;
}

export const addToDate = ( date: Date, type: "minutes" | "hours" | "years", value: number ) => {
    let processed_date = date;

    switch(type){
        case "minutes":
            processed_date.setMinutes( date.getMinutes() + value )
            break;
        case "hours":
            processed_date.setHours( date.getHours() + value )
            break;
        case "years":
            processed_date.setFullYear(date.getFullYear() + value);
            break;
    }

    return processed_date
}


export const isValidJSON = (text: string) => {
    if (typeof text !== "string") {
        return false;
    }
    try {
        JSON.parse(text);
        return true;
    }
    catch (error) {
        return false;
    }
}

var P = {
    l: ['صفر', ' ألف'],
    unis: ['', 'واحد', 'اثنين', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'],
    tens: ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'],
    xtens: ['عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'],
    huns: ['', 'مائة', 'مئتان', 'ثلاث مائة', 'اربع مائة', 'خمس مائة', 'ست مائة', 'سبع مائة', 'ثمان مائة', 'تسع مائة'],
    thos: ['', 'ألف', 'ألفان', 'ثلاثة ألاف', 'اربعة ألاف', 'خمسة ألاف', 'ستة ألاف', 'سبعة ألاف', 'ثمانية ألاف', 'تسعة ألاف'],
    xthos: ['عشرة ألاف', 'أحد عشر ألف', 'اثنا عشر ألف', 'ثلاثة عشر ألف', 'أربعة عشر ألف', 'خمسة عشر ألف', 'ستة عشر ألف', 'سبعة عشر ألف', 'ثمانية عشر ألف', 'تسعة عشر ألف'],
    and: 'و',
};

export const numberToWordsArabic = (y: number) => {
    let s: string = String( y.toString().replace(/[\, ]/g, '') );
    if (s != String( parseFloat(s) )) return y;
    var x = s.indexOf('.'); x = x == -1 ? s.length : x;
    if (x > 6 || s.length - x > 2) return y;
    y = parseFloat(s);
    let d = y - ~~y;
    y = ~~y;
    if (!y) return P.l[0];
    let str = [], r, v = 0, p, c = ~~y % 10, n, i = 1; n = (r = ~~(y / Math.pow(10, i++))) ? r % 10 : undefined;
    do {
        //Units
        if (c > 0) str.push(P.unis[c]);
        if (n === undefined) break; p = c; c = n; n = (r = ~~(y / Math.pow(10, i++))) ? r % 10 : undefined; v += p * Math.pow(10, i - 3);
        //Tens
        if (c == 1) str[0] = P.xtens[p];
        if (c > 1) {
            if (v > 0) str.unshift(P.and);
            str.unshift(P.tens[c]);
        }
        if (n === undefined) break; p = c; c = n; n = (r = ~~(y / Math.pow(10, i++))) ? r % 10 : undefined; v += p * Math.pow(10, i - 3);
        //Hundreds
        if (v > 0 && c > 0) str.push(P.and);
        if (c > 0) str.push(P.huns[c]);
        if (n === undefined) break; p = c; c = n; n = (r = ~~(y / Math.pow(10, i++))) ? r % 10 : undefined; v += p * Math.pow(10, i - 3);
        //Thousands
        if (v > 0 && c > 0 && !n) str.push(P.and);
        if (c > 0 && !n) str.push(P.thos[c]);
        if (n === undefined) break; p = c; c = n; n = (r = ~~(y / Math.pow(10, i++))) ? r % 10 : undefined; v += p * Math.pow(10, i - 3);
        //Ten Thousands
        if (v > 0 && c > 0 && y - c * 1e4 - p * 1e3 > 0) str.push(P.and);
        if (c == 1) str.push(P.xthos[p]);
        if (c > 1) {
            str.push(P.l[1]);
            str.push(P.tens[c]);
            if (p > 0) {
                str.push(P.and);
                str.push(P.unis[p]);
            }
        }
        if (n === undefined) break; p += 10 * c; c = n; n = (r = ~~(y / Math.pow(10, i++))) ? r % 10 : undefined; v += p * Math.pow(10, i - 3);
        //Hundred Thousands
        if (v > 0 && c > 0) str.push(P.and);
        if (c > 0) {
            if (!p) str.push(P.l[1]);
            str.push(P.huns[c]);
        }
    } while (false);
    return str.reverse().join(' ');
}


export const getPercentage = (n: number) => {
    return Number(n * 100).toFixed(2) + "%"
}

export const toFixed = (n: number, digits: number): number => {
    return Number((Math.round(n * 100) / 100).toFixed(digits))
}

export const numberWithCommas = (n: number) => {
    return toFixed(n, 2)?.toString()?.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

let currentYear = (new Date()).getFullYear()
let oldestYear = currentYear - 50
export const years = Array.from({ length: (oldestYear - currentYear) / -1 + 1}, (_, i) => ({ value: currentYear + (i * -1), label: currentYear + (i * -1) }))