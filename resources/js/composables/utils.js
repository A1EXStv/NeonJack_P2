import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'

export default function useUtils() {

    const formatDate = (data) => {
        return new Date(data).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const isClose = (date) => {
        let now = new Date();
        let exerciseCloseD = new Date(date);
        return exerciseCloseD < now;
    };


    return {
        formatDate,
        isClose
    }
}
