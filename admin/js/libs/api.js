

class Api {

    #baseUrl;

    constructor() {
        this.#baseUrl = "../api/";
    }


    async get(endpoint){
        try {
            const rep = fetch(`${this.#baseUrl}${endpoint}`); 
            const data = (await rep).json();
            return data;
        } catch (error) {
            console.log(error);
            return JSON.parse(`{message:fail, error : ${error}}`)
        }
        
    }

    async post(endpoint, postData){
        try {
            const rep = await fetch(`${this.#baseUrl}${endpoint}`, {
                method:"POST", 
                headers:{
                    "Content-Type":"application/json"
                }, 
                body:JSON.stringify(postData)
            });

            const data = rep.json(); 
            return data;
        } catch (error) {
            return JSON.parse(
                `{fail:true, error:${error}}`
            );
        }
    }

}


export const api = new Api();
