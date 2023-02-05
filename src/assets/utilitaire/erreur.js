
export class Erreur
{
    
    #msg = "";
    #code = 200;

    constructor(msg,code)
    {
        this.#msg = msg;
        this.#code = code;
    }

    defini(params) {
        if (typeof params === undefined)
        {
            setMsg("paramêtre non défini : ");
            throw "paramêtre non défini";
        }
    }

    

    getMsg()
    {
        return this.#msg;
    }

    setMsg(msg)
    {
        this.#msg = msg;
    }

    getCode()
    {
        return this.#code;
    }

    setCode(code)
    {
        this.#code = code;
    }
}