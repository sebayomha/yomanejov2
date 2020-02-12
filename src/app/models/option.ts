export class Option {
    hour_start: string;
    hour_finish: string;
    scheduleFrom: Array<string>;
    scheduleTo: Array<string>;
    scheduleSend: Array<string>;
    dir_alt: boolean;

    constructor(hour_start: string, hour_finish: string, scheduleFrom: Array<string>, scheduleTo: Array<string>, scheduleSend: Array<string>, dir_alt: boolean) {
        this.hour_start = hour_start;
        this.hour_finish = hour_finish;
        this.scheduleFrom = scheduleFrom;
        this.scheduleTo = scheduleTo;
        this.scheduleSend = scheduleSend;
        this.dir_alt = dir_alt;
    }
}
