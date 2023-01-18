class Storage {


    constructor() {
        this.storage = window.localStorage;
    }

    set(key, value) {
        return this.storage.setItem(key, value)
    }

    get(key) {
        return this.storage.getItem(key);
    }


    delete(key) {
        return this.storage.removeItem(key)
    }

    all() {
        return Object.keys(localStorage).map(k => localStorage.getItem(k));
    }
}


export {
    Storage
};