import { ref } from 'vue';
import axios from 'axios';
import { da, tr } from 'yup-locales';

export function useSkins() {

    // Almacenar Skins
    const skins = ref([]);
    // Saber si esta cargando
    const isLoading = ref(false);
    // Nuevo estado para creación/edición
    const isSubmitting = ref(false);
    // Para almacenar errores de validación
    const errors = ref({});

    const initialSkin = {
        nombre: '',
        precio: null,
        activo: true,
        imagen: null
    }
    const skin = ref({ ...initialSkin })
    const resetSkin = () => {
        skin.value = { ...initialSkin };
    };
    // Función para obtener skins desde el backend
    const getSkins = async () => {
        isLoading.value = true;
        try {
            const response = await axios.get('/api/skins');
            skins.value = response.data.data;
        } catch (error) {
            console.error("Error cargando skins", error);
        } finally {
            isLoading.value = false;
        }
    };
    // Funcio de skin por id
    const getSkinById = async (id) => {
        return axios.get(`/api/skins/${id}`)
                .then(response => {
                    skin.value = response.data.data;
                    return response;
                }) 
        };
const crearSkin = async (data, imagen) => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        const formData = new FormData();

        // Campos
        formData.append('nombre', data.nombre);
        formData.append('precio', Number(data.precio));
        formData.append('activo', data.activo ? 1 : 0);

        // Imagen
        if (imagen instanceof File) {
            formData.append('picture', imagen);
        }

        console.log('ENVIANDO:', data, imagen);

        const response = await axios.post('/api/skins', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        console.log('RESPUESTA BACKEND:', response.data);

        // 🔥 AQUÍ ESTÁ LA CLAVE
        const nuevaSkin = response.data.data;

        console.log('SKIN FINAL:', nuevaSkin);

        // Guardar en array
        skins.value.push(nuevaSkin);

        return nuevaSkin;

    } catch (error) {
        console.log("ERROR BACKEND:", error.response?.data);

        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error("Error creando skin", error);
        }

        return null;

    } finally {
        isSubmitting.value = false;
    }
};
    //Actulizae skin
    const actualizarSkin = async () => {
        isSubmitting.value = true;
        try {     
            const response = await axios.put(`/api/skins/${skin.value.id}`, skin.value);
           
            // Actualiza el array local
            const index = skins.value.findIndex(s => s.id === skin.value.id);
            if (index !== -1) skins.value[index] = response.data.data;
            return response.data;
        } catch (error) {
            if (error.response && error.response.status === 422) {
                errors.value = error.response.data.errors;
            } else {
                console.error("Error actualizando skin", error);
            }
        } finally {
            isSubmitting.value = false;
        }
    };
    // Funcion para eliminar Skin
    const eliminarSkin = async (id) => {
        try {
            await axios.delete(`/api/skins/${id}`);

            //eliminaos de array local
            skins.value = skins.value.filter(skin => skin.id !== id);
            return true;
        } catch (error) {
            console.error("Error eliminando skin", error);
            return false;
        }
    }


    // devolvwe lo q que quiras usar en la vista
    return {
        skins,
        skin,
        isLoading,
        isSubmitting,
        errors,
        resetSkin,
        getSkins,
        getSkinById,
        crearSkin,
        actualizarSkin,
        eliminarSkin

    };
}