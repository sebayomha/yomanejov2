export class Option {
    hour_start: string;
    hour_finish: string;
    scheduleFrom: Array<string>;
    scheduleTo: Array<string>;

    constructor(hour_start: string, hour_finish: string, scheduleFrom: Array<string>, scheduleTo: Array<string>) {
        this.hour_start = hour_start;
        this.hour_finish = hour_finish;
        this.scheduleFrom = scheduleFrom;
        this.scheduleTo = scheduleTo;
    }
}
