import { Component } from '@angular/core';
import { NgForm } from '@angular/forms';
import { Search } from 'src/app/models/free-class-finder.model';
import { DatesTimes } from 'src/app/models/dates-times';
import { Option } from 'src/app/models/option';



@Component({
  selector: 'free-class-finder',
  templateUrl: './free-class-finder.component.html',
  styleUrls: ['./free-class-finder.component.scss', '../../global-styles.scss']
})

export class FreeClassFinderComponent {

  predefinedHours = ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"];
  search: Search;

  /* search: {
    lessons: number,
    date: Date,
    address: [
      { 'street': string, 'diag': boolean },
      { 'street_a': string, 'diag': boolean },
      { 'street_b': string, 'diag': boolean },
      { 'altitud': string }
    ],
    dates_times: [{
      name_day: string,
      all_day: boolean,
      option: [{
        hour_start: string,
        hour_finish: string,
        scheduleFrom: Array<string>,
        scheduleTo: Array<string>
      }]
    }, {
      name_day: string,
      all_day: boolean,
      option: [{
        hour_start: string,
        hour_finish: string,
        scheduleFrom: Array<string>,
        scheduleTo: Array<string>
      }]
    }, {
      name_day: string,
      all_day: boolean,
      option: [{
        hour_start: string,
        hour_finish: string,
        scheduleFrom: Array<string>,
        scheduleTo: Array<string>
      }]
    }, {
      name_day: string,
      all_day: boolean,
      option: [{
        hour_start: string,
        hour_finish: string,
        scheduleFrom: Array<string>,
        scheduleTo: Array<string>
      }]
    }, {
      name_day: string,
      all_day: boolean,
      option: [{
        hour_start: string,
        hour_finish: string,
        scheduleFrom: Array<string>,
        scheduleTo: Array<string>
      }]
    }, {
      name_day: string,
      all_day: boolean,

      option: [{
        hour_start: string,
        hour_finish: string,
        scheduleFrom: Array<string>,
        scheduleTo: Array<string>
      }]
    }, {
      name_day: string,
      all_day: boolean,
      option: [{
        hour_start: string,
        hour_finish: string,
        scheduleFrom: Array<string>,
        scheduleTo: Array<string>
      }]
    }]
  } */

  constructor() { }

  ngOnInit() {
    let dates_times = new Array<DatesTimes>();
    for (let i = 0; i <=6; i++) {
      let option = new Option('', '', this.predefinedHours, []);
      let options = new Array(option);
      let dateTime = new DatesTimes(this.getDay(i), false, options);
      dates_times.push(dateTime);
    }

    this.search = new Search(dates_times);

    /* //this.search = new Search('Lunes');
    this.search = {
      lessons: null,
      date: new Date(),
      address: [
        { 'street': null, 'diag': null },
        { 'street_a': null, 'diag': null },
        { 'street_b': null, 'diag': null },
        { 'altitud': null }
      ],
      dates_times: [{
        name_day: "Lunes",
        all_day: false,
        option: [{
          hour_start: null,
          hour_finish: null,
          scheduleFrom: this.predefinedHours,
          scheduleTo: []
        }]
      }, {
        name_day: "Martes",
        all_day: false,
        option: [{
          hour_start: null,
          hour_finish: null,
          scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
          scheduleTo: []
        }]
      }, {
        name_day: "Miercoles",
        all_day: false,
        option: [{
          hour_start: null,
          hour_finish: null,
          scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
          scheduleTo: []
        }]
      }, {
        name_day: "Jueves",
        all_day: false,
        option: [{
          hour_start: null,
          hour_finish: null,
          scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
          scheduleTo: []
        }]
      }, {
        name_day: "Viernes",
        all_day: false,
        option: [{
          hour_start: null,
          hour_finish: null,
          scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
          scheduleTo: []
        }]
      }, {
        name_day: "Sabado",
        all_day: false,
        option: [{
          hour_start: null,
          hour_finish: null,
          scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
          scheduleTo: []
        }]
      }, {
        name_day: "Domingo",
        all_day: false,
        option: [{
          hour_start: '',
          hour_finish: '',
          scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
          scheduleTo: []
        }]
      }]
    } */

    console.log(this.search);
  }

  getDay(i: number) {
    switch (i) {
      case 0:
        return 'Lunes';
        break;
      case 1:
        return 'Martes';
        break;
      case 2:
        return 'Miércoles';
        break;
      case 3:
        return 'Jueves';
        break;
      case 4:
        return 'Viernes';
        break;
      case 5:
        return 'Sábado';
        break;
      case 6:
        return 'Domingo';
        break;
    }
  }

  guardar(forma: string) {
    console.log('Forma', forma);

    console.log('Busqueda', this.search);
  }

  allDay(day) {

      let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

      if (index != -1) {
        if (this.search.dates_times[index].all_day == false ) {
          this.search.dates_times[index].all_day = true;
        } else {
          this.search.dates_times[index].all_day = false;
        }
      } 

    console.log('Busqueda', this.search);
  }

  addDateTime(day) {
    let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

    if (index != -1) {
      if(this.search.dates_times[index].option.length == 1){
        if(this.search.dates_times[index].option[0].hour_start == null && this.search.dates_times[index].option[0].hour_finish == null) {
          this.search.dates_times[index].option[0].hour_start = '08:00';
          this.search.dates_times[index].option[0].hour_finish = '09:00';
          this.search.dates_times[index].option[0].scheduleFrom = ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"];
          this.search.dates_times[index].option[0].scheduleTo = [];
        } else {
          this.search.dates_times[index].option.push({
            hour_start: '08:00',
            hour_finish: '09:00',
            scheduleFrom: new Array("08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"),
            scheduleTo: []
          });
        }
      } else {
        this.search.dates_times[index].option.push({
          hour_start: '08:00',
          hour_finish: '09:00',
          scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
          scheduleTo: []
        });
      }       
    }
  }


  doScheduleTo(day,hour,index) {

    this.search.dates_times.forEach(element => {
      if (element.name_day == day) {
        element.option[index].hour_start = hour;
        element.option[index].scheduleFrom.forEach( (h:string) => {
          if (h > hour) {
            element.option[index].scheduleTo.push(h);
          }
        })
      }
    })
  }

  setHourFinish(day,hour) {
    day.hour_finish = hour;
  }

}
