

class Api {

    #baseUrl;

    constructor() {
        this.#baseUrl = "../api/";
    }


    /**
     * 
     * @param {string} endpoint 
     * @returns return a response from the api
     */
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
    /**
     * 
     * @param {string} endpoint 
     * @param {Object} postData 
     * @returns return a response from the api and send the posted data to the api
     */
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

    /**
     * 
     * @param {string} dateString 
     * @returns a php date string to a js date string
     */
    datePhpToDateJs(dateString){
        const date = new Date(dateString); 

        const year =  date.getFullYear(); 
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const munites = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${munites}`;
    }

}


export const api = new Api();
