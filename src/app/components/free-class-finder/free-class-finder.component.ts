import { Component } from '@angular/core';
import { NgForm } from '@angular/forms';



@Component({
  selector: 'free-class-finder',
  templateUrl: './free-class-finder.component.html',
  styleUrls: ['./free-class-finder.component.scss', '../../global-styles.scss']
})

export class FreeClassFinderComponent {

  search = {
    lessons: null,
    date: null,
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
        scheduleFrom: [],
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
        hour_start: null,
        hour_finish: null,
        scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
        scheduleTo: []
      }]
    }]
  }

  constructor() { }

  guardar(forma: any) {
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
            scheduleFrom: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"],
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
        element.option[index].scheduleFrom.forEach(h => {
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
