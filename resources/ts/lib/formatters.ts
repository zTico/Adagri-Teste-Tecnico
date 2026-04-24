export function formatCpfCnpj(value: string | null | undefined): string {
    if (!value) {
        return '-';
    }

    const digits = value.replace(/\D/g, '');

    if (digits.length === 11) {
        return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    if (digits.length === 14) {
        return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }

    return value;
}

export function maskCpfCnpj(value: string | null | undefined): string {
    const digits = (value ?? '').replace(/\D/g, '').slice(0, 14);

    if (!digits) {
        return '';
    }

    if (digits.length <= 11) {
        if (digits.length <= 3) {
            return digits;
        }

        if (digits.length <= 6) {
            return digits.replace(/(\d{3})(\d+)/, '$1.$2');
        }

        if (digits.length <= 9) {
            return digits.replace(/(\d{3})(\d{3})(\d+)/, '$1.$2.$3');
        }

        return digits.replace(/(\d{3})(\d{3})(\d{3})(\d+)/, '$1.$2.$3-$4');
    }

    if (digits.length <= 2) {
        return digits;
    }

    if (digits.length <= 5) {
        return digits.replace(/(\d{2})(\d+)/, '$1.$2');
    }

    if (digits.length <= 8) {
        return digits.replace(/(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
    }

    if (digits.length <= 12) {
        return digits.replace(/(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
    }

    return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d+)/, '$1.$2.$3/$4-$5');
}

export function formatPhone(value: string | null | undefined): string {
    if (!value) {
        return '-';
    }

    const digits = value.replace(/\D/g, '');

    if (digits.length === 11) {
        return digits.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }

    if (digits.length === 10) {
        return digits.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }

    return value;
}

export function maskPhone(value: string | null | undefined): string {
    const digits = (value ?? '').replace(/\D/g, '').slice(0, 11);

    if (!digits) {
        return '';
    }

    if (digits.length <= 2) {
        return digits;
    }

    if (digits.length <= 6) {
        return digits.replace(/(\d{2})(\d+)/, '($1) $2');
    }

    if (digits.length <= 10) {
        return digits.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
    }

    return digits.replace(/(\d{2})(\d{5})(\d+)/, '($1) $2-$3');
}

export function formatPostalCode(value: string | null | undefined): string {
    if (!value) {
        return '-';
    }

    const digits = value.replace(/\D/g, '');

    if (digits.length === 8) {
        return digits.replace(/(\d{5})(\d{3})/, '$1-$2');
    }

    return value;
}

export function maskPostalCode(value: string | null | undefined): string {
    const digits = (value ?? '').replace(/\D/g, '').slice(0, 8);

    if (digits.length <= 5) {
        return digits;
    }

    return digits.replace(/(\d{5})(\d+)/, '$1-$2');
}

export function formatEnumLabel(value: string | null | undefined): string {
    if (!value) {
        return '-';
    }

    const labels: Record<string, string> = {
        swine: 'Suinos',
        goats: 'Caprinos',
        cattle: 'Bovinos',
        breeding: 'Criacao',
        meat: 'Corte',
        milk: 'Leite',
        mixed: 'Misto',
    };

    if (labels[value]) {
        return labels[value];
    }

    return value
        .split('_')
        .join(' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase())
    ;
}
