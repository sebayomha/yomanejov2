import { DatesTimes } from "./dates-times";

export class Search {

    lessons: number;
    date: Date;
    address: [{ street: string, diag: boolean },
        { street_a: string, diag: boolean },
        { street_b: string, diag: boolean },
        { altitud: string }];
    dates_times: Array<DatesTimes>

    constructor(datesTime: Array<DatesTimes>, lessons?:number, date?:Date) {
        this.lessons = lessons;
        this.date = date;
        this.address = [
                { 'street': '', 'diag': false },
                { 'street_a': '', 'diag': false },
                { 'street_b': '', 'diag': false },
                { 'altitud': '' }
              ];
        this.dates_times = datesTime;
    }
}
