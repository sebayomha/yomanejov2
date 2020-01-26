export class Address {
    street: string;
    diag: boolean;
    street_a: string;
    street_b: string;
    altitud: string;
    city: string

    constructor(street?: string, diag?: boolean, street_a?: string, street_b?: string, altitud?: string, city?: string) {
        this.street = street;
        this.diag = diag;
        this.street_a = street_a;
        this.street_b = street_b;
        this.altitud = altitud;
        this.city = city;
    }
}
