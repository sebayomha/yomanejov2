import { Component, Input, ElementRef } from '@angular/core';
declare var $:any;

@Component({
  selector: 'custom-modal',
  templateUrl: './custom-modal.component.html',
  styleUrls: ['./custom-modal.component.scss']
})

export class CustomModalComponent {

    constructor() { }

    ngOnInit() { }

    open() {
      $('#confirmModal').modal('show');
    }
}