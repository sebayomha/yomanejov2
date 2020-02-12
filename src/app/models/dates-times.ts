import { Option } from "./option";

export class DatesTimes {
    name_day: string;
    all_day: boolean;
    option: Array<Option>;
    dir_alt: boolean;

    constructor(name_day: string, all_day: boolean, option: Array<any>, dir_alt: boolean) {
        this.name_day = name_day;
        this.all_day = all_day;
        this.option = option;
        this.dir_alt = dir_alt;
    }
}
