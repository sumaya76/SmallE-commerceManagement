import { reactive } from 'vue';
import api from './api';

function readUser() {
    try {
        return JSON.parse(localStorage.getItem('user'));
    } catch (e) {
        return null;
    }
}

export const auth = reactive({
    token: localStorage.getItem('token'),
    user: readUser(),
});

function setSession(token, user) {
    auth.token = token;
    auth.user = user;
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
}

function clearSession() {
    auth.token = null;
    auth.user = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
}

export async function login(email, password) {
    const { data } = await api.post('/login', { email, password });
    setSession(data.token, data.user);
    return data.user;
}

export async function logout() {
    try {
        await api.post('/logout');
    } catch (e) {
        // token already invalid hole-o local session clear hobe
    }
    clearSession();
}

export function homeFor(user) {
    if (user?.role === 'admin') return '/admin';
    if (user?.role === 'shop_owner') return '/owner';
    return '/';
}