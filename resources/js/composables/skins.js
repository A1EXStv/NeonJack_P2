import { ref } from 'vue';
import axios from 'axios';
import { tr } from 'yup-locales';

export  function useSkins() {

    // Almacenar Skins
    const skins = ref([]);
    // Saber si esta cargando
    const isLoading = ref(false);
    // Nuevo estado para creación/edición
    const isSubmitting = ref(false);
     // Para almacenar errores de validación
    const errors = ref({});


    // Función para obtener skins desde el backend
    const getSkins = async () => {
        isLoading.value = true;
        try {
            const response = await axios.get('/api/skins');
            skins.value = response.data;
        } catch (error) {
            console.error("Error cargando skins", error);
        } finally {
            isLoading.value = false;
        }
    };
    //Funcion para crear una skin
    const crearSkin = async (data) => {
        isSubmitting.value = true;
        errors.value = {};
        try {
            const response = await axios.post('/api/skins', data);
            // añadimos nueva Skin a la array
            skins.value.push(response.data);
            return response.data;
        } catch (error){
             if (error.response && error.response.status === 422) {
                // Validación Laravel
                errors.value = error.response.data.errors; 
            } else {
                console.error("Error creando skin", error);
            }
        } finally {
             isSubmitting.value = false;
        }
    }
    // Funcion para eliminar Skin
    const eliminarSkin = async (id) => {
        try {
            await axios.delete(`/api/skins/${id}`);

            //eliminaos de array local
            skins.value = skins.value.filter(skin => skin.id !== id);
            return true;
        }catch (error) {
            console.error("Error eliminando skin", error);
             return false;        }
    }


    // devolvwe lo q que quiras usar en la vista
    return {
        skins,
        isLoading,
        isSubmitting,
        errors,
        getSkins,
        crearSkin,
        eliminarSkin
        
    };
}