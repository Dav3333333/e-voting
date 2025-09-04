
class Func{

    constructor(){

    }

    isNumber(val) {
        return /^-?\d+(\.\d+)?$/.test(val);
    }

    reformatPhpDate(dateStr){
        const date = new Date(dateStr.replace(' ', 'T'));
        return date.toLocaleString('fr-FR');
    }

}

export const func = new Func();