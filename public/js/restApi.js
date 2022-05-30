/**
 * @brief REST API interface class.
 * 
 * Provides an interface to a REST API.
 */
 class RestApi {
    constructor(api_url){
        this._api_url = api_url;
        if(this._api_url !== "" && this._api_url[this._api_url.length-1] !== '/')
            this._api_url += '/';
    }


    _url_from_uri(uri){
        return `${this._api_url}${uri}`;
    }

    get(uri){
        return fetch(
            this._url_from_uri(uri),
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
            }
        );
    }
    
    post(uri, params){
        let data = JSON.stringify(params);
        return fetch(
            this._url_from_uri(uri),
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: data
            }
        );
    }
    
    
    delete(uri){
        return fetch(
            this._url_from_uri(uri),
            {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            }
        );
    }
};