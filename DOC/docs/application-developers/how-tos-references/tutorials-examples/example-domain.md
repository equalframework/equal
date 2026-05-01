
# Using Date References in Domains

When working with eQual, date references provide a powerful way to dynamically filter and manipulate data based on time intervals. These references allow you to describe and retrieve specific dates relative to the current date, making it easier to handle scenarios like scheduling, reporting, and data filtering.

The examples below demonstrate how to use date references in various contexts. Each example highlights a specific use case, such as retrieving the first day of a month, calculating future or past dates, or pinpointing specific days of a week within a given interval.


## Examples of Date References

### Today
Retrieve the current day.

```
date.this.day
```

### Seven Days from Now
Calculate the date exactly seven days in the future.

```
date.next(7).day
```

### First Day of the Month 5 Months Before the Current Month

Determine the first day of the month, five months prior to the current month.

```
date.prev(5).month.first()
```

### Last Day of the Quarter 2 Quarters After the Current Quarter

Find the last day of the quarter, two quarters ahead of the current quarter.

```
date.next(2).quarter.last()
```

### 34th Week of the Next Year

Retrieve the 34th week of the year following the current year.

```
date.next(1).year.get(week:34)
```

### First Monday of the Semester 3 Semesters Before the Current Semester

Identify the first Monday of the semester, three semesters before the current semester.

```
date.prev(3).semester.get(monday:first)
```

### Second Wednesday of the Month 4 Months After the Current Month

Pinpoint the second Wednesday of the month, four months after the current month.

```
date.next(4).month.get(wednesday:2)
```

### First Day of the Current Year

Retrieve the first day of the current year. This can be achieved in two ways:

```
date.this.year.first
```

Or:

```
date.this.year.get(day:1)
```

---
