export class Address {
    street: string;
    diag: boolean;
    street_a: string;
    street_b: string;
    altitud: string;
    city: string;
    floor: string;
    department: string;
    observations: string;


    constructor(street?: string, diag?: boolean, street_a?: string, street_b?: string, altitud?: string, city?: string, floor?: string, department?: string, observations?: string) {
        this.street = street;
        this.diag = diag;
        this.street_a = street_a;
        this.street_b = street_b;
        this.altitud = altitud;
        this.city = city;
        this.floor = floor;
        this.department = department;
        this.observations = observations;
    }
}
