// Laravel error response theke validation errors ar message ber kore
export function parseError(e) {
    if (e.response?.status === 422) {
        return {
            errors: e.response.data.errors || {},
            message: e.response.data.message || 'Validation failed.',
        };
    }

    return {
        errors: {},
        message: e.response?.data?.message || 'Something went wrong. Please try again.',
    };
}

export function formatTaka(amount) {
    return `৳ ${Number(amount).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}