import { ExcepcionRowTIme } from "./excepcion-row-time";

export interface Excepcion {
    date: Date;
    date_string: string;
    no_puede: boolean;
    horarios: Array<ExcepcionRowTIme>;
}
