import axios from 'axios';
import { ref } from "vue";
import { defineStore } from "pinia";

export const authStore = defineStore("authStore", () => {

    let user = ref({name:''});
    let authenticated = ref(false);

    async function login(data) {
        axios.get('/api/user').then(response => {
            user.value = response.data.data
            authenticated.value = true
        }).catch(error => {
            user.value = {}
            authenticated.value = false
        })
    }
    async function getUser(data) {

        await axios.get('/api/user').then(response => {
            user.value = response.data.data
            authenticated.value = true
            console.log('getUser AT: true ');
            console.log(user.value);
        }).catch(error => {
            console.log('getUser: error ');
            user.value = {}
            authenticated.value = false
        })
    }

    async function getUserSignIn(data) {
        await axios.get('/api/user/signin').then(response => {
            user.value = response.data.data
            authenticated.value = true
        }).catch(error => {
            console.log('getUserSignIn: error ');
            user.value = {}
            authenticated.value = false
        })
    }
    function logout() {
        user.value = {}
        authenticated.value = false
    }

    function is(roleName) {
        return user.value.roles.some(role => role.name === roleName);
    }

    return { user, authenticated, login, is, getUser,getUserSignIn, logout};
}, {persist: true});
