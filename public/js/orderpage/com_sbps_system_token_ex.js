var tokenResponse = class {
    constructor() {
       this.token = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
       this.tokenKey = "ddddddddddddddddddddddddddddddd";
       this.maskedCcMumber = "************1234";
       this.ccExpiration = "202106";
       this.cardBrandCode = "V";
    }
}
///*
var responseclass = class {
    constructor() {
       this.result = "OK";
       this.tokenResponse = new tokenResponse();
    }
}
//*/
/*
var responseclass = class {
    constructor() {
       this.result = "NG";
       this.errorCode = "07003";
    }
}
*/
function genetateToken({merchantId = '', serviceId = '', ccNumber = '', ccExpiration = '', securityCode = ''}, afterGenetateToken) {
    var myResponce = new responseclass();
    afterGenetateToken(myResponce);
}
