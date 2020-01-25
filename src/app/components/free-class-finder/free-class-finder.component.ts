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

  locations = ["La Plata","Berisso","Ensenada"];
  predefinedHours = ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"];
  search: Search;
  address_complete:boolean = false;
  control_flag_empty:boolean = false;

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

    if (!this.control_flag_empty) {
      let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

      if (index != -1) {
        if(this.search.dates_times[index].option.length == 1 && this.search.dates_times[index].option[0].hour_start == '' && this.search.dates_times[index].option[0].hour_finish == ''){

            this.search.dates_times[index].option[0].hour_start = 'not_assigned';
            this.search.dates_times[index].option[0].hour_finish = 'not_assigned';
            this.search.dates_times[index].option[0].scheduleFrom = ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"];
            this.search.dates_times[index].option[0].scheduleTo = [];

        } else {
          let option_length = this.search.dates_times[index].option.length;

          let j = option_length - 1;

          let hour_finish_selected = this.search.dates_times[index].option[j].hour_finish;

          let new_schedule_from = [];

          this.search.dates_times[index].option[j].scheduleFrom.forEach( (h:string) => {
            if (h > hour_finish_selected ) {
              new_schedule_from.push(h);
            }
          })

          this.search.dates_times[index].option.push({
            hour_start: 'not_assigned',
            hour_finish: 'not_assigned',
            scheduleFrom: new_schedule_from,
            scheduleTo: []
          }); 
        }       
      }
      
      console.log('Busqueda', this.search);

      this.control_flag_empty = true;
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

  setHourFinish(option,hour) {
    option.hour_finish = hour;
    this.control_flag_empty = false;
  }

  selectionCity(city) {
    this.search.address[4].city = city;
  }

  isAddressFull() {
    if(this.search.address[1].street_a != null || this.search.address[2].street_b != null || this.search.address[3].altitud != null) return this.address_complete = true; return this.address_complete = false;
  }

  removeOption(index, day_options) {
    
    if (day_options.length > 1) {
    day_options.splice( index, 1 );
    } else {
      day_options[index].hour_start = '';
      day_options[index].hour_finish = '';
      day_options[index].scheduleTo = [];
    }
    this.control_flag_empty = false;
    console.log('Busqueda', this.search);
  }
  

}
