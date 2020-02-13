import { Option } from "./option";

export class DatesTimes {
    name_day: string;
    all_day: boolean;
    option: Array<Option>;

    constructor(name_day: string, all_day: boolean, option: Array<any>) {
        this.name_day = name_day;
        this.all_day = all_day;
        this.option = option;
    }
}
