

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

    /** mus have a formData object to success */
    async post(endpoint, postData){
        try {
            const rep = await fetch(`${this.#baseUrl}${endpoint}`, {
                method:"POST", 
                body:postData
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
