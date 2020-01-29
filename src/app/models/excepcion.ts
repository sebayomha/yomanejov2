import { ExcepcionRowTIme } from "./excepcion-row-time";

export interface Excepcion {
    date: Date;
    date_string: String;
    horarios: Array<ExcepcionRowTIme>;
}
