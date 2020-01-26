import { DatesTimes } from "./dates-times";
import { Address } from "./address.model";

export class Search {

    lessons: number;
    date: Date;
    address:Array<Address>;
    dates_times: Array<DatesTimes>

    constructor(datesTime: Array<DatesTimes>, address:Array<Address>, lessons?:number, date?:Date) {
        this.lessons = lessons;
        this.date = date;
        this.address = address;
        this.dates_times = datesTime;
    }
}
