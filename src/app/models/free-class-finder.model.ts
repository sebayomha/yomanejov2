import { DatesTimes } from "./dates-times";
import { Address } from "./address.model";

export class Search {

    student_name:string;
    lessons: number;
    date: Date;
    address:Array<Address>;
    dates_times: Array<DatesTimes>;
    observations:string;

    constructor(student_name:string, datesTime: Array<DatesTimes>, address:Array<Address>, lessons?:number, date?:Date, observations?:string) {
        this.student_name = student_name;
        this.lessons = lessons;
        this.date = date;
        this.address = address;
        this.dates_times = datesTime;
        this.observations = observations;
    }
}
