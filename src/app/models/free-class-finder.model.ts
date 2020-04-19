import { DatesTimes } from "./dates-times";
import { Address } from "./address.model";

export class Search {

    student_name: string;
    student_phone: number;
    lessons: number;
    date: Date;
    address:Array<Address>;
    address_alternative:Array<Address>;
    dates_times: Array<DatesTimes>;

    constructor(student_name:string, datesTime: Array<DatesTimes>, address:Array<Address>, address_alternative:Array<Address> , lessons?:number, date?:Date, student_phone?: number) {
        this.student_name = student_name;
        this.lessons = lessons;
        this.date = date;
        this.address = address;
        this.address_alternative = address_alternative;
        this.dates_times = datesTime;
        this.student_phone = student_phone;
    }
}
