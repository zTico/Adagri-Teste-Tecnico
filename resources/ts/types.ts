export interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'viewer';
    role_label: string;
}

export interface OptionItem {
    label: string;
    value: string;
}

export interface LookupEntity {
    id: number;
    name: string;
    rural_producer_id?: number;
}

export interface LocationLookup {
    state: string;
    cities: string[];
}

export interface RuralProducer {
    id: number;
    name: string;
    cpf_cnpj: string;
    phone: string | null;
    email: string | null;
    farms_count?: number | null;
    address: {
        postal_code: string;
        street: string;
        number: string;
        complement: string | null;
        district: string | null;
        city: string;
        state: string;
    };
    created_at: string;
}

export interface Farm {
    id: number;
    name: string;
    city: string;
    state: string;
    state_registration: string | null;
    total_area: number;
    herds_count?: number | null;
    total_animals?: number | null;
    rural_producer?: {
        id: number;
        name: string;
    } | null;
}

export interface Herd {
    id: number;
    species: string;
    quantity: number;
    purpose: string;
    farm?: {
        id: number;
        name: string;
        rural_producer_id: number;
    } | null;
    rural_producer?: {
        id: number;
        name: string;
    } | null;
    updated_at: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}

export interface ResourceResponse<T> {
    data: T;
}

export interface LookupPayload {
    species: OptionItem[];
    purposes: OptionItem[];
    rural_producers: LookupEntity[];
    farms: LookupEntity[];
    producer_locations: LocationLookup[];
    farm_locations: LocationLookup[];
}

export interface PostalCodeLookupPayload {
    postal_code: string;
    street: string | null;
    complement: string | null;
    district: string | null;
    city: string | null;
    state: string | null;
}

export interface ReportsPayload {
    totals: {
        rural_producers: number;
        farms: number;
        animals: number;
    };
    farms_by_city: Array<{
        city: string;
        state: string;
        total: number;
    }>;
    animals_by_species: Array<{
        species: string;
        total: number;
    }>;
}

export interface ApiValidationError {
    message: string;
    errors?: Record<string, string[]>;
}
