
export default function useFilters() {

    const CONTAINS_IN_ARRAY = (value, filter) => {

        if(value && filter) {
            if (filter.length == 0) return true;
            const value_r = value.map(function (e) {return e.id;});
            const filter_r = filter.map(function (e) {return e.id;});
            return value_r.some(r => filter_r.includes(r))
        }
        return false;
    };

    const CONTAINS_ALL_IN_ARRAY = (value, filter) => {
        if (value && filter) {
            if (filter.length === 0) return true; // filtro vacío => pasa todo
            const value_r = value.map(e => e.id);   // ids de los values
            const filter_r = filter.map(e => e.id); // ids del filtro
            return filter_r.every(r => value_r.includes(r));
        }
        return false;
    };

    return {
        CONTAINS_IN_ARRAY,
        CONTAINS_ALL_IN_ARRAY
    }
}
