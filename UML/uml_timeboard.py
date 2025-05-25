from graphviz import Digraph

# Create a new directed graph
dot = Digraph(comment="UML - TimeBoard", format="png")
dot.attr(rankdir="LR", fontsize="12")

# User entity
dot.node('User', '''<<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0">
<TR><TD COLSPAN="2"><B>User</B></TD></TR>
<TR><TD>+ id : int</TD></TR>
<TR><TD>+ email : string</TD></TR>
<TR><TD>+ roles : array</TD></TR>
<TR><TD>+ password : string</TD></TR>
<TR><TD>+ firstName : string</TD></TR>
<TR><TD>+ lastName : string</TD></TR>
<TR><TD>+ createdAt : DateTimeImmutable</TD></TR>
<TR><TD>+ isActive : bool</TD></TR>
</TABLE>>''', shape="record")

# WorkMonth entity
dot.node('WorkMonth', '''<<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0">
<TR><TD COLSPAN="2"><B>WorkMonth</B></TD></TR>
<TR><TD>+ id : int</TD></TR>
<TR><TD>+ user : User</TD></TR>
<TR><TD>+ month : int</TD></TR>
<TR><TD>+ year : int</TD></TR>
<TR><TD>+ createdAt : DateTimeImmutable</TD></TR>
</TABLE>>''')

# WorkDay entity
dot.node('WorkDay', '''<<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0">
<TR><TD COLSPAN="2"><B>WorkDay</B></TD></TR>
<TR><TD>+ id : int</TD></TR>
<TR><TD>+ workMonth : WorkMonth</TD></TR>
<TR><TD>+ date : DateTimeImmutable</TD></TR>
<TR><TD>+ isFullDay : bool</TD></TR>
<TR><TD>+ hasLunchTicket : bool</TD></TR>
</TABLE>>''')

# WorkPeriod entity
dot.node('WorkPeriod', '''<<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0">
<TR><TD COLSPAN="2"><B>WorkPeriod</B></TD></TR>
<TR><TD>+ id : int</TD></TR>
<TR><TD>+ workDay : WorkDay</TD></TR>
<TR><TD>+ label : string</TD></TR>
<TR><TD>+ start : TimeImmutable</TD></TR>
<TR><TD>+ end : TimeImmutable</TD></TR>
<TR><TD>+ duration : int</TD></TR>
<TR><TD>+ location : string</TD></TR>
<TR><TD>+ replacedAgent : string|null</TD></TR>
</TABLE>>''')

# Relationships
dot.edge("User", "WorkMonth", label="1..*")
dot.edge("WorkMonth", "WorkDay", label="1..*")
dot.edge("WorkDay", "WorkPeriod", label="1..*")

dot.save(filename='structure.dot')
dot.render(filename='structure', format='png', cleanup=True)
